<?php

namespace Tests\Feature;

use App\Mail\ApplicantResumeMail;
use App\Mail\PageMailer;
use App\Mail\PreservedNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

final class WorkflowTest extends TestCase
{
    use DatabaseTransactions;

    private array $createdFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        } parent::tearDown();
    }

    private function user(string $role): User
    {
        $u = new User;
        $u->forceFill(['fullname' => 'Workflow Test', 'username' => 'flow_'.bin2hex(random_bytes(5)), 'email' => bin2hex(random_bytes(5)).'@example.test', 'password' => 'workflow-password', 'role' => $role, 'is_active' => 1, 'status' => 'approved', 'course' => 'BSIS', 'batch_year' => '2025', 'trainings' => 'Software development', 'address' => 'Calapan', 'employer_company' => 'Test Company']);
        $u->save();

        return $u;
    }

    public function test_admin_can_create_hashed_accounts_and_approve_a_graduate(): void
    {
        Mail::fake();
        $this->actingAs($this->user('admin'));
        foreach (['employer', 'alumni_officer'] as $role) {
            $data = ['fullname' => 'Test Account', 'company' => 'Test Company', 'email' => $role.'@example.test', 'username' => 'created_'.$role, 'password' => 'created-password', 'confirm_password' => 'created-password', 'is_active' => 1];
            $this->post('/admin/create_'.$role.'.php', $data)->assertOk();
            $u = User::where('username', 'created_'.$role)->firstOrFail();
            $this->assertSame($role, $u->role);
            $this->assertTrue(Hash::check('created-password', $u->password));
        }
        $alumni = $this->user('alumni');
        $alumni->is_active = 0;
        $alumni->status = 'pending';
        $alumni->save();
        $this->post('/admin/pending_alumni.php', ['user_id' => $alumni->id, 'action' => 'approve'])->assertOk();
        $this->assertSame('approved', $alumni->fresh()->status);
        $this->assertTrue($alumni->fresh()->is_active);
        Mail::assertQueued(PreservedNotification::class);
    }

    public function test_compatibility_mailer_preserves_sender_bcc_and_plain_text(): void
    {
        Mail::fake();
        $mailer = new PageMailer;
        $mailer->setFrom('sender@example.test', 'GradConn Sender');
        $mailer->addAddress('recipient@example.test', 'Recipient');
        $mailer->addBCC('hidden@example.test', 'Hidden Recipient');
        $mailer->Subject = 'Delivery test';
        $mailer->Body = '<p>HTML message</p>';
        $mailer->AltBody = 'Plain message';
        $mailer->send();

        Mail::assertQueued(PreservedNotification::class, 1);
        $mail = Mail::queued(PreservedNotification::class)->first();
        $mail->build();
        $this->assertTrue($mail->hasTo('recipient@example.test'));
        $this->assertTrue($mail->hasBcc('hidden@example.test'));
        $this->assertTrue($mail->hasFrom('sender@example.test'));
        $this->assertSame('Plain message', $mail->plainText);
    }

    public function test_admin_can_queue_an_applicant_resume_from_private_storage(): void
    {
        Mail::fake();
        $admin = $this->user('admin');
        $alumni = $this->user('alumni');
        $jobId = DB::table('jobs')->insertGetId([
            'title' => 'Resume forwarding test', 'company' => 'Test Company',
            'employer_company' => 'Test Company', 'description' => 'Test',
            'posted_by' => $admin->id, 'is_open' => 1,
        ]);
        $filename = 'forward-'.bin2hex(random_bytes(4)).'.pdf';
        Storage::disk('local')->put('files/uploads/resumes/'.$filename, '%PDF-1.4 test');
        $this->createdFiles[] = Storage::disk('local')->path('files/uploads/resumes/'.$filename);
        $applicationId = DB::table('applications')->insertGetId([
            'job_id' => $jobId, 'alumni_id' => $alumni->id,
            'resume_file' => $filename, 'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.applications.resume.send', $applicationId), [
            'company_email' => 'company@example.test',
        ])->assertRedirect();

        Mail::assertQueued(ApplicantResumeMail::class, fn ($mail) => $mail->hasTo('company@example.test'));
    }

    public function test_admin_account_creation_rejects_invalid_and_duplicate_data(): void
    {
        $this->actingAs($this->user('admin'));

        $this->post('/admin/create_employer.php', [
            'fullname' => 'Invalid Employer', 'company' => 'Company', 'email' => 'not-an-email',
            'username' => 'invalid_employer', 'password' => 'valid-password',
        ])->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['username' => 'invalid_employer']);

        $existing = $this->user('alumni_officer');
        $this->post('/admin/create_alumni_officer.php', [
            'fullname' => 'Duplicate Officer', 'email' => $existing->email, 'username' => $existing->username,
            'password' => 'valid-password', 'confirm_password' => 'valid-password', 'is_active' => 1,
        ])->assertSessionHasErrors(['email', 'username']);
        $this->post('/admin/create_alumni_officer.php', [
            'fullname' => 'Weak Password', 'email' => 'weak@example.test', 'username' => 'weak_password',
            'password' => 'short', 'confirm_password' => 'short', 'is_active' => 1,
        ])->assertSessionHasErrors('password');

        $this->post('/admin/alumni_create.php', [
            'fullname' => 'Invalid Alumni', 'student_id' => 'invalid-course-user', 'email' => 'alumni@example.test',
            'course' => 'Unknown Course', 'batch_year' => date('Y'), 'password' => 'valid-password',
        ])->assertSessionHasErrors('course');
        $this->assertDatabaseMissing('users', ['username' => 'invalid-course-user']);
    }

    public function test_admin_alumni_edit_validates_input_and_hashes_replacement_password(): void
    {
        $admin = $this->user('admin');
        $alumni = $this->user('alumni');
        $path = '/admin/alumni_edit.php?id='.$alumni->id;

        $this->actingAs($admin)->post($path, [
            'fullname' => 'Updated Graduate',
            'email' => 'invalid-email',
            'course' => 'BSIS',
            'batch_year' => '2025',
            'is_active' => '1',
            'password' => 'replacement-password',
        ])->assertSessionHasErrors('email');
        $this->assertNotSame('Updated Graduate', $alumni->fresh()->fullname);

        $this->post($path, [
            'fullname' => 'Updated Graduate',
            'email' => 'updated-graduate@example.test',
            'course' => 'BSIS',
            'batch_year' => '2025',
            'is_active' => '1',
            'password' => 'replacement-password',
        ])->assertOk();

        $alumni->refresh();
        $this->assertSame('Updated Graduate', $alumni->fullname);
        $this->assertTrue(Hash::check('replacement-password', $alumni->password));
    }

    public function test_admin_and_employer_can_post_jobs(): void
    {
        Mail::fake();
        $this->user('alumni');
        foreach (['admin', 'employer'] as $role) {
            $user = $this->user($role);
            $data = ['title' => 'Workflow '.$role.' job', 'location' => 'Calapan', 'job_type' => 'Full-time', 'start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d', strtotime('+30 days')), 'description' => 'Test job description', 'is_open' => 1, 'employer_company' => 'Test Company', 'email_address' => $user->email];
            $this->actingAs($user)->post($role === 'admin' ? '/admin/jobs_create.php' : '/employer/post_job.php', $data)->assertOk();
            $this->assertDatabaseHas('jobs', ['title' => $data['title'], 'posted_by' => $user->id, 'is_open' => 1]);
        }
        Mail::assertQueued(PreservedNotification::class);
    }

    public function test_application_upload_review_and_download_are_scoped(): void
    {
        Mail::fake();
        $employer = $this->user('employer');
        $alumni = $this->user('alumni');
        $jobId = DB::table('jobs')->insertGetId(['title' => 'Application workflow', 'company' => 'Test Company', 'employer_company' => 'Test Company', 'target_course' => 'BSIS', 'description' => 'Test', 'posted_by' => $employer->id, 'employer_id' => $employer->id, 'is_open' => 1]);
        $upload = UploadedFile::fake()->createWithContent('resume.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");
        $this->actingAs($alumni)->post('/alumni/apply.php?job_id='.$jobId, ['message' => 'Application test', 'agree_terms' => 1, 'resume' => $upload])->assertOk();
        $application = DB::table('applications')->where('job_id', $jobId)->where('alumni_id', $alumni->id)->first();
        $this->assertNotNull($application);
        $path = storage_path('app/private/files/uploads/resumes/'.$application->resume_file);
        $this->createdFiles[] = $path;
        $this->assertFileExists($path);
        $this->actingAs($employer)->get('/employer/applications.php?view_resume='.urlencode($application->resume_file))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->post('/employer/applications.php', ['application_id' => $application->id, 'action' => 'interview', 'action_message' => 'Please attend the interview.'])->assertOk();
        $this->assertDatabaseHas('applications', ['id' => $application->id, 'status' => 'interview']);
        $this->actingAs($this->user('employer'))->post('/employer/applications.php', ['application_id' => $application->id, 'action' => 'accept', 'action_message' => 'Unauthorized action'])->assertOk();
        $this->assertDatabaseHas('applications', ['id' => $application->id, 'status' => 'interview']);
    }

    public function test_job_details_cannot_bypass_the_complete_application_flow(): void
    {
        $employer = $this->user('employer');
        $alumni = $this->user('alumni');
        $jobId = DB::table('jobs')->insertGetId([
            'title' => 'Complete application required',
            'company' => 'Test Company',
            'employer_company' => 'Test Company',
            'description' => 'Test',
            'posted_by' => $employer->id,
            'employer_id' => $employer->id,
            'is_open' => 1,
        ]);

        $this->actingAs($alumni)
            ->post('/alumni/job_details.php?id='.$jobId)
            ->assertRedirect('/alumni/apply.php?job_id='.$jobId);

        $this->assertDatabaseMissing('applications', ['job_id' => $jobId, 'alumni_id' => $alumni->id]);
    }

    public function test_direct_application_urls_respect_job_dates(): void
    {
        $employer = $this->user('employer');
        $alumni = $this->user('alumni');
        foreach ([
            ['title' => 'Future job', 'start_date' => date('Y-m-d', strtotime('+2 days')), 'end_date' => date('Y-m-d', strtotime('+10 days'))],
            ['title' => 'Expired job', 'start_date' => date('Y-m-d', strtotime('-10 days')), 'end_date' => date('Y-m-d', strtotime('-2 days'))],
        ] as $dates) {
            $jobId = DB::table('jobs')->insertGetId(array_merge($dates, [
                'company' => 'Test Company',
                'employer_company' => 'Test Company',
                'description' => 'Test',
                'posted_by' => $employer->id,
                'employer_id' => $employer->id,
                'is_open' => 1,
            ]));

            $this->actingAs($alumni)
                ->get('/alumni/apply.php?job_id='.$jobId)
                ->assertOk()
                ->assertSee('This job is not currently accepting applications.');
        }
    }

    public function test_authorized_employer_can_download_a_retained_word_resume(): void
    {
        if (! class_exists(ZipArchive::class)) {
            $this->markTestSkipped('ZipArchive is required to create a DOCX fixture.');
        }

        $employer = $this->user('employer');
        $alumni = $this->user('alumni');
        $jobId = DB::table('jobs')->insertGetId([
            'title' => 'Legacy document test',
            'company' => 'Test Company',
            'employer_company' => 'Test Company',
            'description' => 'Test',
            'posted_by' => $employer->id,
            'employer_id' => $employer->id,
            'is_open' => 1,
        ]);
        $filename = 'retained_'.bin2hex(random_bytes(5)).'.docx';
        $path = storage_path('app/private/files/uploads/resumes/'.$filename);
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"></Types>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0"?><document></document>');
        $zip->close();
        $this->createdFiles[] = $path;
        DB::table('applications')->insert([
            'job_id' => $jobId,
            'alumni_id' => $alumni->id,
            'resume_file' => $filename,
            'status' => 'pending',
        ]);

        $this->actingAs($employer)
            ->get('/uploads/resumes/'.$filename)
            ->assertOk()
            ->assertDownload($filename);
    }

    public function test_events_trainings_and_education_can_be_created(): void
    {
        Mail::fake();
        $admin = $this->user('admin');
        $this->actingAs($admin)->post('/admin/events_create.php', ['title' => 'Workflow event', 'content' => 'Event description'])->assertOk();
        $this->assertDatabaseHas('events', ['title' => 'Workflow event', 'posted_by' => $admin->id]);
        $this->post('/admin/trainings_create.php', ['title' => 'Workflow training', 'content' => 'Training description', 'training_date' => date('Y-m-d'), 'target_course' => 'BSIS', 'location' => 'Campus'])->assertOk();
        $training = DB::table('trainings')->where('title', 'Workflow training')->first();
        $this->assertNotNull($training);
        $this->get('/admin/trainings_edit.php?id='.$training->id)->assertOk();
        $alumni = $this->user('alumni');
        $this->actingAs($alumni)->post('/alumni/add_degree.php', ['add_education' => 1, 'school_name' => 'Test College', 'degree' => 'Tertiary', 'start_year' => '2021', 'end_year' => '2025'])->assertOk();
        $this->assertDatabaseHas('alumni_education', ['user_id' => $alumni->id, 'school_name' => 'Test College']);
    }

    public function test_admin_training_deletion_uses_the_named_delete_route(): void
    {
        $admin = $this->user('admin');
        $trainingId = DB::table('trainings')->insertGetId([
            'title' => 'Delete route test', 'content' => 'Test',
            'training_date' => date('Y-m-d'), 'target_course' => 'BSIS',
            'posted_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.trainings.destroy', $trainingId))
            ->assertRedirect(route('admin.trainings_list'));

        $this->assertDatabaseMissing('trainings', ['id' => $trainingId]);
    }

    public function test_offer_tokens_persist_between_requests_and_offers_expire(): void
    {
        Mail::fake();
        $employer = $this->user('employer');
        $alumni = $this->user('alumni');
        $this->actingAs($employer)->get('/employer/alumni_list.php')->assertOk();
        $state = session('page_state');
        $token = $state['send_snapshot_email_token'] ?? null;
        // Use the rendered field name to preserve the existing double-submit protection.
        if (! $token) {
            foreach ($state as $key => $value) {
                if (str_contains($key, 'token')) {
                    $token = $value;
                }
            }
        }
        $this->assertNotEmpty($token);
        $this->post('/employer/alumni_list.php', ['send_snapshot_email' => 1, 'send_snapshot_email_token' => $token, 'email_alumni_id' => $alumni->id, 'email_subject' => 'Test offer', 'email_message' => 'A test job offer.'])->assertOk();
        $offer = DB::table('job_offers')->where('employer_id', $employer->id)->where('alumni_id', $alumni->id)->first();
        $this->assertNotNull($offer);
        DB::table('job_offers')->where('id', $offer->id)->update(['expires_at' => date('Y-m-d H:i:s', strtotime('-1 day'))]);
        $this->actingAs($alumni)->post('/alumni/job_offers.php', ['offer_id' => $offer->id, 'offer_action' => 'accept'])->assertStatus(422);
        $this->assertDatabaseHas('job_offers', ['id' => $offer->id, 'status' => 'sent']);
    }

    public function test_employment_history_uses_laravel_transactions(): void
    {
        $alumni = $this->user('alumni');
        $this->actingAs($alumni)->post('/alumni/employment_history.php', ['add_employment' => 1, 'company_name' => 'Employment Test', 'job_title' => 'Developer', 'start_date' => '2026-01-01', 'end_date' => '', 'employment_type' => 'Full-time', 'location' => 'Calapan', 'job_description' => 'Software development'])->assertOk();
        $this->assertDatabaseHas('employment_history', ['user_id' => $alumni->id, 'company_name' => 'Employment Test']);
    }
}
