<?php

namespace Tests\Feature;

use App\Mail\AlumniAccountApprovedMail;
use App\Mail\ApplicantResumeMail;
use App\Mail\JobOpportunityMail;
use App\Models\User;
use Database\Seeders\AlumniOfficerSeeder;
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
        $u->forceFill(['fullname' => 'Workflow Test', 'username' => 'flow_'.bin2hex(random_bytes(5)), 'email' => bin2hex(random_bytes(5)).'@example.test', 'password' => 'workflow-password', 'role' => $role, 'is_active' => 1, 'status' => 'approved', 'course' => 'BSIS', 'batch_year' => '2025', 'address' => 'Calapan', 'employer_company' => 'Test Company']);
        $u->save();

        return $u;
    }

    public function test_admin_can_create_hashed_accounts_and_approve_a_graduate(): void
    {
        Mail::fake();
        $this->actingAs($this->user('admin'));
        foreach (['employer', 'alumni_officer'] as $role) {
            $data = ['fullname' => 'Test Account', 'company' => 'Test Company', 'email' => $role.'@example.test', 'username' => 'created_'.$role, 'password' => 'created-password', 'confirm_password' => 'created-password', 'is_active' => 1];
            $endpoint = $role === 'employer' ? '/admin/employers' : '/admin/alumni-officers';
            $this->post($endpoint, $data)->assertRedirect();
            $u = User::where('username', 'created_'.$role)->firstOrFail();
            $this->assertSame($role, $u->role);
            $this->assertTrue(Hash::check('created-password', $u->password));
        }
        $alumni = $this->user('alumni');
        $alumni->is_active = 0;
        $alumni->status = 'pending';
        $alumni->save();
        $this->patch('/admin/alumni/'.$alumni->id.'/approval')->assertRedirect();
        $this->assertSame('approved', $alumni->fresh()->status);
        $this->assertTrue($alumni->fresh()->is_active);
        Mail::assertQueued(AlumniAccountApprovedMail::class);
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

        $this->post('/admin/employers', [
            'fullname' => 'Invalid Employer', 'company' => 'Company', 'email' => 'not-an-email',
            'username' => 'invalid_employer', 'password' => 'valid-password',
        ])->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['username' => 'invalid_employer']);

        $existing = $this->user('alumni_officer');
        $this->post('/admin/alumni-officers', [
            'fullname' => 'Duplicate Officer', 'email' => $existing->email, 'username' => $existing->username,
            'password' => 'valid-password', 'confirm_password' => 'valid-password', 'is_active' => 1,
        ])->assertSessionHasErrors(['email', 'username']);
        $this->post('/admin/alumni-officers', [
            'fullname' => 'Weak Password', 'email' => 'weak@example.test', 'username' => 'weak_password',
            'password' => 'short', 'confirm_password' => 'short', 'is_active' => 1,
        ])->assertSessionHasErrors('password');

        $this->post('/admin/alumni', [
            'fullname' => 'Invalid Alumni', 'student_id' => 'invalid-course-user', 'email' => 'alumni@example.test',
            'course' => 'Unknown Course', 'batch_year' => date('Y'), 'password' => 'valid-password',
        ])->assertSessionHasErrors('course');
        $this->assertDatabaseMissing('users', ['username' => 'invalid-course-user']);
    }

    public function test_admin_alumni_edit_validates_input_and_hashes_replacement_password(): void
    {
        $admin = $this->user('admin');
        $alumni = $this->user('alumni');
        $path = '/admin/alumni/'.$alumni->id;

        $this->actingAs($admin)->put($path, [
            'fullname' => 'Updated Graduate',
            'email' => 'invalid-email',
            'course' => 'BSIS',
            'batch_year' => '2025',
            'is_active' => '1',
            'password' => 'replacement-password',
        ])->assertSessionHasErrors('email');
        $this->assertNotSame('Updated Graduate', $alumni->fresh()->fullname);

        $this->put($path, [
            'fullname' => 'Updated Graduate',
            'email' => 'updated-graduate@example.test',
            'course' => 'BSIS',
            'batch_year' => '2025',
            'is_active' => '1',
            'password' => 'replacement-password',
        ])->assertRedirect('/admin/alumni_edit?id='.$alumni->id);

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
            $data = ['title' => 'Workflow '.$role.' job', 'location' => 'Calapan', 'job_type' => 'Full-time', 'start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d', strtotime('+30 days')), 'description' => 'Test job description', 'requirements' => 'Communication and technical competencies', 'target_course' => 'BSIS', 'is_open' => 1, 'employer_company' => 'Test Company', 'email_address' => $user->email];
            $this->actingAs($user)->post('/jobs', $data)->assertRedirect();
            $this->assertDatabaseHas('jobs', ['title' => $data['title'], 'posted_by' => $user->id, 'is_open' => 1]);
        }
        Mail::assertQueued(JobOpportunityMail::class);
    }

    public function test_application_upload_review_and_download_are_scoped(): void
    {
        Mail::fake();
        $employer = $this->user('employer');
        $alumni = $this->user('alumni');
        $jobId = DB::table('jobs')->insertGetId(['title' => 'Application workflow', 'company' => 'Test Company', 'employer_company' => 'Test Company', 'target_course' => 'BSIS', 'description' => 'Test', 'posted_by' => $employer->id, 'employer_id' => $employer->id, 'is_open' => 1]);
        $upload = UploadedFile::fake()->createWithContent('resume.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");
        $this->actingAs($alumni)->post('/alumni/jobs/'.$jobId.'/applications', ['message' => 'Application test', 'agree_terms' => 1, 'resume' => $upload])->assertSessionHasNoErrors()->assertRedirect();
        $application = DB::table('applications')->where('job_id', $jobId)->where('alumni_id', $alumni->id)->first();
        $this->assertNotNull($application);
        $path = storage_path('app/private/files/uploads/resumes/'.$application->resume_file);
        $this->createdFiles[] = $path;
        $this->assertFileExists($path);
        $this->actingAs($this->user('admin'))->get(route('applications.letter', $application->id))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($employer)->get(route('applications.letter', $application->id))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($employer)->get('/employer/applications?view_resume='.urlencode($application->resume_file))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->patch('/applications/'.$application->id.'/status', ['action' => 'interview', 'action_message' => 'Please attend the interview.'])->assertRedirect();
        $this->assertDatabaseHas('applications', ['id' => $application->id, 'status' => 'interview']);
        $this->actingAs($this->user('employer'))->patch('/applications/'.$application->id.'/status', ['action' => 'accept', 'action_message' => 'Unauthorized action'])->assertForbidden();
        $this->assertDatabaseHas('applications', ['id' => $application->id, 'status' => 'interview']);
    }

    public function test_application_picker_survives_multiline_profile_content(): void
    {
        $employer = $this->user('employer');
        $alumni = $this->user('alumni');
        $alumni->forceFill([
            'contact_number' => '09123456789',
            'career_objective' => "Build reliable systems.\nWork with O'Brien's team.",
            'skills' => "PHP\nJavaScript",
        ])->save();
        $jobId = DB::table('jobs')->insertGetId([
            'title' => 'Safe picker rendering', 'company' => 'Test Company',
            'employer_company' => 'Test Company', 'description' => 'Test',
            'posted_by' => $employer->id, 'employer_id' => $employer->id, 'is_open' => 1,
        ]);

        $this->actingAs($alumni)
            ->get(route('alumni.apply', ['job_id' => $jobId]))
            ->assertOk()
            ->assertSee('for="resumeInput"', false)
            ->assertSee('id="fileNameDisplay"', false)
            ->assertSee('No PDF file selected')
            ->assertSee('const profileData = JSON.parse(', false);
    }

    public function test_alumni_job_pages_prevent_duplicate_applications_and_show_the_existing_state(): void
    {
        $employer = $this->user('employer');
        $alumni = $this->user('alumni');
        $jobId = DB::table('jobs')->insertGetId([
            'title' => 'No duplicate applications', 'company' => 'Test Company', 'employer_company' => 'Test Company',
            'description' => 'Test', 'posted_by' => $employer->id, 'employer_id' => $employer->id, 'is_open' => 1,
        ]);
        DB::table('applications')->insert(['job_id' => $jobId, 'alumni_id' => $alumni->id, 'status' => 'pending']);

        $this->actingAs($alumni)->get(route('alumni.jobs'))
            ->assertOk()->assertSee('Already Applied')->assertDontSee('/alumni/apply?job_id='.$jobId, false);
        $this->get(route('alumni.job_details', ['id' => $jobId]))
            ->assertOk()->assertSee('You already applied')->assertDontSee('Apply Now');
        $this->get(route('alumni.apply', ['job_id' => $jobId]))
            ->assertOk()->assertSee('You already applied for this opportunity')->assertDontSee('id="applicationForm"', false);

        $upload = UploadedFile::fake()->createWithContent('application.pdf', "%PDF-1.4\n%%EOF");
        $this->post(route('applications.store', $jobId), ['agree_terms' => 1, 'resume' => $upload])
            ->assertSessionHasErrors('resume');
        $this->assertSame(1, DB::table('applications')->where('job_id', $jobId)->where('alumni_id', $alumni->id)->count());
    }

    public function test_alumni_can_store_a_certificate_and_see_validation_errors(): void
    {
        $alumni = $this->user('alumni');
        $originalDisk = config('filesystems.uploads_disk');
        config(['filesystems.uploads_disk' => 'local']);
        Storage::fake('local');

        try {
            $certificate = UploadedFile::fake()->createWithContent(
                'certificate.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true)
            );
            $this->actingAs($alumni)->post(route('profile.certificates.store'), [
                'certificate_name' => 'Laravel Certificate', 'issue_date' => today()->toDateString(), 'certificate_image' => $certificate,
            ])->assertSessionHasNoErrors()->assertRedirect(route('profile'));

            $record = DB::table('alumni_certificates')->where('user_id', $alumni->id)->where('certificate_name', 'Laravel Certificate')->first();
            $this->assertNotNull($record);
            Storage::disk('local')->assertExists('files/uploads/certificates/'.$record->certificate_image);

            $this->from(route('profile'))->post(route('profile.certificates.store'), [])->assertSessionHasErrors(['certificate_name', 'certificate_image']);
        } finally {
            config(['filesystems.uploads_disk' => $originalDisk]);
        }
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

        $this->actingAs($alumni)->get('/alumni/job_details?id='.$jobId)
            ->assertOk()->assertSee('/alumni/apply?job_id='.$jobId, false);

        $this->post('/alumni/job_details?id='.$jobId)->assertMethodNotAllowed();

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
                ->get('/alumni/apply?job_id='.$jobId)
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

    public function test_events_and_education_can_be_created(): void
    {
        Mail::fake();
        $admin = $this->user('admin');
        $this->actingAs($admin)->post('/events', [
            'category' => 'announcement',
            'title' => '𝗔𝗡𝗡𝗢𝗨𝗡𝗖𝗘𝗠𝗘𝗡𝗧: Institution-Wide Orientation',
            'content' => 'Orientation begins at 𝟴:𝟯𝟬 AM for all students.',
            'post_start_date' => '',
            'post_end_date' => '',
        ])->assertRedirect(route('admin.events_create'));
        $this->assertDatabaseHas('events', ['title' => '𝗔𝗡𝗡𝗢𝗨𝗡𝗖𝗘𝗠𝗘𝗡𝗧: Institution-Wide Orientation', 'posted_by' => $admin->id]);
        $alumni = $this->user('alumni');
        $this->actingAs($alumni)->post('/profile/education', ['add_education' => 1, 'school_name' => 'Test College', 'degree' => 'Tertiary', 'start_year' => '2021', 'end_year' => '2025'])->assertRedirect();
        $this->assertDatabaseHas('alumni_education', ['user_id' => $alumni->id, 'school_name' => 'Test College']);
    }

    public function test_events_index_redirects_each_role_to_its_event_destination(): void
    {
        $destinations = [
            'admin' => 'admin.events_list',
            'alumni_officer' => 'alumni_officer.events_list',
            'alumni' => 'alumni.feed',
            'employer' => 'employer.dashboard',
        ];

        foreach ($destinations as $role => $route) {
            $this->actingAs($this->user($role))
                ->get(route('events.index'))
                ->assertRedirect(route($route));
        }
    }

    public function test_event_upload_storage_failure_returns_a_form_error(): void
    {
        $admin = $this->user('admin');
        $originalDisk = config('filesystems.uploads_disk');
        config(['filesystems.uploads_disk' => 'unconfigured-test-disk']);

        try {
            $this->actingAs($admin)->from(route('admin.events_create'))->post(route('events.store'), [
                'category' => 'event',
                'title' => 'Event with unavailable storage',
                'content' => 'The event must not be partially created.',
                'post_start_date' => '',
                'post_end_date' => '',
                'image' => UploadedFile::fake()->createWithContent(
                    'event.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true)
                ),
            ])->assertRedirect(route('admin.events_create'))->assertSessionHasErrors('image');
        } finally {
            config(['filesystems.uploads_disk' => $originalDisk]);
        }

        $this->assertDatabaseMissing('events', ['title' => 'Event with unavailable storage']);
    }

    public function test_profile_update_tolerates_omitted_optional_employment_status(): void
    {
        $alumni = $this->user('alumni');
        $alumni->forceFill(['employment_status' => 'Unemployed'])->save();

        $this->actingAs($alumni)->put(route('profile.update'), [
            'fullname' => 'Updated Alumni Name',
        ])->assertRedirect(route('profile'));

        $this->assertDatabaseHas('users', [
            'id' => $alumni->id,
            'fullname' => 'Updated Alumni Name',
            'employment_status' => 'Unemployed',
        ]);
    }

    public function test_event_requests_validate_dates_and_enforce_update_policy(): void
    {
        $admin = $this->user('admin');
        $officer = $this->user('alumni_officer');

        $this->actingAs($admin)->post(route('events.store'), [
            'category' => 'event',
            'title' => 'Invalid scheduled event', 'content' => 'Test',
            'post_start_date' => '2026-10-10 10:00:00',
            'post_end_date' => '2026-10-09 10:00:00',
        ])->assertSessionHasErrors('post_end_date');
        $this->assertDatabaseMissing('events', ['title' => 'Invalid scheduled event']);

        $eventId = DB::table('events')->insertGetId([
            'title' => 'Admin owned event', 'content' => 'Original', 'posted_by' => $admin->id,
        ]);
        $this->actingAs($officer)->put(route('events.update', $eventId), [
            'category' => 'announcement',
            'title' => 'Unauthorized update', 'content' => 'Changed',
        ])->assertForbidden();
        $this->assertDatabaseHas('events', ['id' => $eventId, 'title' => 'Admin owned event']);

        $this->actingAs($admin)->put(route('events.update', $eventId), [
            'category' => 'news',
            'title' => 'Updated event', 'content' => 'Changed',
            'post_start_date' => '', 'post_end_date' => '',
        ])->assertRedirect(route('admin.events_edit', ['id' => $eventId]));
        $this->assertDatabaseHas('events', ['id' => $eventId, 'title' => 'Updated event']);
    }

    public function test_event_deletion_uses_an_authorized_resource_route(): void
    {
        $officer = $this->user('alumni_officer');
        $otherOfficer = $this->user('alumni_officer');
        $eventId = DB::table('events')->insertGetId([
            'title' => 'Delete event route test', 'content' => 'Test', 'posted_by' => $officer->id,
        ]);

        $this->actingAs($officer)
            ->get(route('alumni_officer.dashboard'))
            ->assertOk()
            ->assertSee(route('events.destroy', $eventId), false);

        $this->actingAs($otherOfficer)
            ->delete(route('events.destroy', $eventId))
            ->assertForbidden();
        $this->assertDatabaseHas('events', ['id' => $eventId]);

        $this->actingAs($officer)
            ->delete(route('events.destroy', $eventId))
            ->assertRedirect(route('alumni_officer.events_list'));
        $this->assertDatabaseMissing('events', ['id' => $eventId]);
    }

    public function test_admin_job_deletion_uses_the_named_delete_route(): void
    {
        $admin = $this->user('admin');
        $jobId = DB::table('jobs')->insertGetId([
            'title' => 'Delete job route test', 'company' => 'Test Company',
            'employer_company' => 'Test Company', 'description' => 'Test',
            'posted_by' => $admin->id, 'is_open' => 1,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.jobs.destroy', $jobId))
            ->assertRedirect(route('admin.jobs_list'));
        $this->assertDatabaseMissing('jobs', ['id' => $jobId]);
    }

    public function test_alumni_can_delete_only_their_certificate_through_the_resource_route(): void
    {
        $owner = $this->user('alumni');
        $other = $this->user('alumni');
        $certificateId = DB::table('alumni_certificates')->insertGetId([
            'user_id' => $owner->id, 'certificate_name' => 'Delete test', 'issuer' => 'GradConn',
        ]);

        $this->actingAs($other)
            ->delete(route('profile.certificates.destroy', $certificateId))
            ->assertForbidden();
        $this->assertDatabaseHas('alumni_certificates', ['id' => $certificateId]);

        $this->actingAs($owner)
            ->delete(route('profile.certificates.destroy', $certificateId))
            ->assertRedirect(route('profile'));
        $this->assertDatabaseMissing('alumni_certificates', ['id' => $certificateId]);
    }

    public function test_archived_event_restore_uses_policy_and_patch_route(): void
    {
        $officer = $this->user('alumni_officer');
        $otherOfficer = $this->user('alumni_officer');
        $eventId = DB::table('events')->insertGetId([
            'title' => 'Restore route test', 'content' => 'Test',
            'posted_by' => $officer->id, 'is_archived' => 1,
            'archived_at' => now(),
        ]);

        $this->actingAs($otherOfficer)
            ->patch(route('events.restore', $eventId))
            ->assertForbidden();
        $this->assertDatabaseHas('events', ['id' => $eventId, 'is_archived' => 1]);

        $this->actingAs($officer)
            ->patch(route('events.restore', $eventId))
            ->assertRedirect(route('alumni_officer.archive'));
        $this->assertDatabaseHas('events', ['id' => $eventId, 'is_archived' => 0]);

        $this->actingAs($otherOfficer)
            ->patch(route('events.archive', $eventId))
            ->assertForbidden();
        $this->actingAs($officer)
            ->patch(route('events.archive', $eventId))
            ->assertRedirect(route('alumni_officer.events_list'));
        $this->assertDatabaseHas('events', ['id' => $eventId, 'is_archived' => 1]);
    }

    public function test_alumni_employment_deletion_updates_status_and_checks_ownership(): void
    {
        $owner = $this->user('alumni');
        $other = $this->user('alumni');
        $owner->forceFill(['employment_status' => 'Employed'])->save();
        $employmentId = DB::table('employment_history')->insertGetId([
            'user_id' => $owner->id, 'company_name' => 'Test Company',
            'job_title' => 'Developer', 'start_date' => '2025-01-01', 'end_date' => null,
        ]);

        $this->actingAs($other)
            ->delete(route('alumni.employment.destroy', $employmentId))
            ->assertForbidden();
        $this->assertDatabaseHas('employment_history', ['id' => $employmentId]);

        $this->actingAs($owner)
            ->delete(route('alumni.employment.destroy', $employmentId))
            ->assertRedirect(route('alumni.employment_history'));
        $this->assertDatabaseMissing('employment_history', ['id' => $employmentId]);
        $this->assertSame('Unemployed', $owner->fresh()->employment_status);
        $this->assertDatabaseHas('security_logs', [
            'user_id' => $owner->id, 'action' => 'EMPLOYMENT_HISTORY_DELETED',
        ]);
    }

    public function test_employment_history_uses_laravel_transactions(): void
    {
        $alumni = $this->user('alumni');
        $this->actingAs($alumni)->post('/profile/employment', ['add_employment' => 1, 'company_name' => 'Employment Test', 'job_title' => 'Developer', 'start_date' => '2026-01-01', 'end_date' => '', 'employment_type' => 'Full-time', 'location' => 'Calapan', 'job_description' => 'Software development'])->assertRedirect();
        $this->assertDatabaseHas('employment_history', ['user_id' => $alumni->id, 'company_name' => 'Employment Test']);
    }

    public function test_panelist_course_list_and_removed_offer_workflow_are_enforced(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('BLIS');

        $employer = $this->user('employer');
        $this->actingAs($employer)->get('/employer/alumni_list')->assertOk()->assertSee('Professional Directory');
        $this->actingAs($employer)->get('/employer/job_offers')->assertRedirect(route('employer.applications'), 301);
        $this->actingAs($employer)->post('/employer/offers', [])->assertNotFound();
    }

    public function test_community_post_category_is_saved_and_job_email_opt_out_is_respected(): void
    {
        Mail::fake();
        $admin = $this->user('admin');
        $subscribed = $this->user('alumni');
        $unsubscribed = $this->user('alumni');
        $unsubscribed->forceFill(['receive_update_notifications' => 0])->save();

        $this->actingAs($admin)->post(route('events.store'), [
            'category' => 'news', 'title' => 'Campus update', 'content' => 'A verified campus update.',
        ])->assertRedirect();
        $this->assertDatabaseHas('events', ['title' => 'Campus update', 'category' => 'news']);

        $this->actingAs($admin)->post(route('jobs.store'), [
            'title' => 'Information Systems Analyst', 'description' => 'Maintain campus information systems.',
            'requirements' => 'Systems analysis and communication', 'target_course' => 'BSIS',
            'location' => 'Calapan City', 'job_type' => 'Full-time', 'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(), 'is_open' => 1,
        ])->assertRedirect();

        Mail::assertQueued(JobOpportunityMail::class, fn (JobOpportunityMail $mail) => $mail->recipient->is($subscribed));
        Mail::assertNotQueued(JobOpportunityMail::class, fn (JobOpportunityMail $mail) => $mail->recipient->is($unsubscribed));
    }

    public function test_employer_application_view_exposes_only_application_relevant_profile_data(): void
    {
        $employer = $this->user('employer');
        $alumni = $this->user('alumni');
        $alumni->forceFill(['address' => 'PRIVATE HOME ADDRESS', 'indigenous_tribe' => 'PRIVATE TRIBE', 'special_needs' => 'PRIVATE MEDICAL DATA', 'skills' => 'Laravel, communication'])->save();
        $this->actingAs($employer)->get(route('employer.alumni_list'))
            ->assertOk()->assertSee($alumni->fullname)->assertSee('Laravel, communication')
            ->assertDontSee($alumni->email)->assertDontSee('PRIVATE HOME ADDRESS')->assertDontSee('PRIVATE TRIBE')->assertDontSee('PRIVATE MEDICAL DATA');
        $jobId = DB::table('jobs')->insertGetId([
            'title' => 'Privacy Test Job', 'company' => 'Test Company', 'employer_company' => 'Test Company',
            'description' => 'Test', 'posted_by' => $employer->id, 'employer_id' => $employer->id, 'is_open' => 1,
        ]);
        DB::table('applications')->insert([
            'job_id' => $jobId, 'alumni_id' => $alumni->id, 'status' => 'pending',
            'applicant_fullname' => $alumni->fullname, 'applicant_email' => $alumni->email,
            'applicant_course' => $alumni->course, 'applicant_batch_year' => $alumni->batch_year,
            'applicant_skills' => $alumni->skills,
        ]);

        $this->actingAs($employer)->get(route('employer.applications'))
            ->assertOk()->assertSee('Laravel, communication')
            ->assertDontSee('PRIVATE HOME ADDRESS')->assertDontSee('PRIVATE TRIBE')->assertDontSee('PRIVATE MEDICAL DATA');
    }

    public function test_single_alumni_officer_can_be_seeded_and_manage_events_and_password(): void
    {
        User::query()->where('role', 'alumni_officer')->delete();
        config()->set('gradconn.alumni_officer_seed', [
            'name' => 'Campus Alumni Officer', 'username' => 'campus_officer',
            'email' => 'officer@example.test', 'password' => 'SecureOfficer123',
        ]);
        $this->seed(AlumniOfficerSeeder::class);
        $this->seed(AlumniOfficerSeeder::class);

        $officer = User::query()->where('role', 'alumni_officer')->firstOrFail();
        $this->assertSame(1, User::query()->where('role', 'alumni_officer')->count());
        $this->assertTrue(Hash::check('SecureOfficer123', $officer->password));
        $admin = $this->user('admin');
        $this->actingAs($admin)->post(route('admin.alumni-officers.store'), [
            'fullname' => 'Second Officer', 'username' => 'second_officer', 'email' => 'second@example.test',
            'password' => 'SecondOfficer123', 'confirm_password' => 'SecondOfficer123', 'is_active' => 1,
        ])->assertSessionHasErrors('username');
        $this->assertSame(1, User::query()->where('role', 'alumni_officer')->count());
        $this->actingAs($officer)->get(route('alumni_officer.dashboard'))->assertOk()->assertSee('Alumni Officer Workspace')->assertSee('Change Password');
        $this->post(route('events.store'), ['category' => 'announcement', 'title' => 'Officer announcement', 'content' => 'Posted by the officer.'])->assertRedirect(route('alumni_officer.events_create'));
        $this->assertDatabaseHas('events', ['title' => 'Officer announcement', 'posted_by' => $officer->id]);
        $this->put(route('profile.password.update'), ['old_password' => 'SecureOfficer123', 'new_password' => 'ChangedOfficer123', 'confirm_password' => 'ChangedOfficer123'])
            ->assertRedirect('/profile?tab=security');
        $this->assertTrue(Hash::check('ChangedOfficer123', $officer->fresh()->password));
    }
}
