<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

final class MigrationTest extends TestCase
{
    use DatabaseTransactions;

    private array $createdFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    private function user(string $role = 'alumni', array $attributes = []): User
    {
        $u = new User;
        $u->forceFill(array_merge(['fullname' => 'Migration Test', 'username' => 'test_'.bin2hex(random_bytes(5)), 'email' => bin2hex(random_bytes(5)).'@example.test', 'password' => 'test-password-123', 'role' => $role, 'is_active' => 1, 'status' => 'approved', 'course' => 'BSIS', 'batch_year' => '2025'], $attributes));
        $u->save();

        return $u;
    }

    public function test_registration_hashes_password_and_requires_approval(): void
    {
        $this->post('/register.php', ['fullname' => 'New Graduate', 'student_id' => 'migration-registration', 'email' => 'registration@example.test', 'course' => 'BSIS', 'batch_year' => '2025', 'password' => 'new-password-123', 'confirm_password' => 'new-password-123'])->assertRedirect('/register.php');
        $user = User::where('username', 'migration-registration')->firstOrFail();
        $this->assertTrue(Hash::check('new-password-123', $user->password));
        $this->assertFalse($user->is_active);
        $this->assertSame('pending', $user->status);
        $this->post('/', ['student_id' => $user->username, 'password' => 'new-password-123'])->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_validation_redirects_retain_safe_form_input(): void
    {
        $this->from('/register.php')->post('/register.php', [
            'fullname' => 'Remembered Graduate',
            'student_id' => '',
            'email' => 'remembered@example.test',
            'course' => 'BSIS',
            'batch_year' => '2025',
            'password' => 'new-password-123',
            'confirm_password' => 'new-password-123',
        ])->assertRedirect('/register.php');

        $this->get('/register.php')
            ->assertSee('Remembered Graduate')
            ->assertSee('remembered@example.test');
    }

    public function test_login_displays_flash_status_messages(): void
    {
        $this->withSession(['status' => 'Password reset successful. Please sign in.'])
            ->get('/')
            ->assertSee('Password reset successful. Please sign in.');
    }

    public function test_logout_uses_an_in_page_modal_and_remains_post_only(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)
            ->get('/admin/dashboard.php')
            ->assertOk()
            ->assertSee('id="logoutLightbox"', false)
            ->assertSee('data-logout-trigger', false)
            ->assertSee('Log out of GradConn?');

        $this->get('/auth/logout.php')->assertRedirect('/');
        $this->assertAuthenticatedAs($admin);
        $this->post('/auth/logout.php')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_public_auth_links_and_legacy_page_aliases_route_correctly(): void
    {
        $this->get('/auth/admin_login.php')
            ->assertOk()
            ->assertSee(route('password.request'), false)
            ->assertSee(route('register'), false);

        $this->get('/register.php')->assertOk()->assertSee(route('login'), false);
        $this->get('/reset_password.php')->assertOk()->assertSee(route('login'), false);

        $admin = $this->user('admin');
        $this->actingAs($admin)
            ->get('/admin/employer_list.php')
            ->assertRedirect('/admin/create_employer.php');

        $employer = $this->user('employer');
        $this->actingAs($employer)
            ->get('/employer/my_jobs.php')
            ->assertRedirect('/employer/posted_job.php');
        $this->get('/employer/job_list.php')->assertRedirect('/employer/posted_job.php');
        $this->get('/employer/jobl_list.php')->assertRedirect('/employer/posted_job.php');
    }

    public function test_read_only_pages_and_compatibility_redirects_reject_post_requests(): void
    {
        $cases = [
            'admin' => ['/admin/dashboard.php', '/admin/alumni_report.php', '/admin/graduates_list.php', '/admin/graduates_report.php', '/admin/graduates_stats.php', '/admin/jobs_list.php', '/admin/offers_history.php', '/admin/reports.php', '/admin/employer_list.php'],
            'alumni' => ['/alumni/dashboard.php', '/alumni/jobs.php'],
            'alumni_officer' => ['/alumni_officer/dashboard.php', '/archive.php'],
            'employer' => ['/employer/dashboard.php', '/employer/my_jobs.php', '/employer/job_list.php', '/employer/jobl_list.php'],
        ];

        foreach ($cases as $role => $paths) {
            $this->actingAs($this->user($role));
            foreach ($paths as $path) {
                $this->post($path)->assertMethodNotAllowed();
            }
        }
    }

    public function test_all_login_aliases_use_the_same_guard_and_block_inactive_accounts(): void
    {
        $user = $this->user('employer');
        $this->post('/auth/login.php', ['username' => $user->username, 'password' => 'test-password-123'])->assertRedirect('/employer/dashboard.php');
        $this->assertAuthenticatedAs($user);
        $user->is_active = false;
        $user->save();
        $this->get('/employer/dashboard.php')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_password_change_verifies_hash_and_stores_another_hash(): void
    {
        $user = $this->user();
        DB::table('sessions')->insert([
            'id' => 'another-device-session',
            'user_id' => $user->id,
            'payload' => 'expired test session',
            'last_activity' => time(),
        ]);
        $this->actingAs($user)->post('/alumni/change_password.php', ['old_password' => 'test-password-123', 'new_password' => 'replacement-password', 'confirm_password' => 'replacement-password'])->assertOk();
        $this->assertTrue(Hash::check('replacement-password', $user->fresh()->password));
        $this->assertDatabaseMissing('sessions', ['id' => 'another-device-session']);
        $this->assertDatabaseHas('security_logs', ['user_id' => $user->id, 'action' => 'PASSWORD_CHANGED']);
    }

    public function test_admin_can_change_password_from_profile_and_log_in_again(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)->post('/profile.php', [
            'old_password' => 'test-password-123',
            'new_password' => 'replacement-admin-password',
            'confirm_password' => 'replacement-admin-password',
        ])->assertOk()->assertSee('Password changed successfully!');

        $this->assertTrue(Hash::check('replacement-admin-password', $admin->fresh()->password));
        Auth::logout();

        $this->post('/', [
            'username' => $admin->username,
            'password' => 'replacement-admin-password',
        ])->assertRedirect('/admin/dashboard.php');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_roles_cannot_access_each_others_portals(): void
    {
        foreach (['admin', 'alumni', 'employer', 'alumni_officer'] as $role) {
            $this->actingAs($this->user($role));
            foreach (array_diff(['admin', 'alumni', 'employer', 'alumni_officer'], [$role]) as $other) {
                $this->get('/'.$other.'/dashboard.php')->assertForbidden();
            }
        }
    }

    public function test_resume_access_requires_application_ownership(): void
    {
        $owner = $this->user('employer');
        $alumni = $this->user();
        $jobId = DB::table('jobs')->insertGetId([
            'title' => 'Private resume test',
            'company' => 'Owner Company',
            'employer_company' => 'Owner Company',
            'description' => 'Test',
            'posted_by' => $owner->id,
            'employer_id' => $owner->id,
            'is_open' => 1,
        ]);
        $filename = 'ownership_'.bin2hex(random_bytes(5)).'.pdf';
        $path = storage_path('app/private/files/uploads/resumes/'.$filename);
        file_put_contents($path, "%PDF-1.4\n%%EOF");
        $this->createdFiles[] = $path;
        $application = new JobApplication;
        $application->forceFill([
            'job_id' => $jobId,
            'alumni_id' => $alumni->id,
            'resume_file' => $filename,
            'status' => 'pending',
        ])->save();
        $application = $application->fresh();
        $this->actingAs($this->user('employer'))->get('/employer/applications.php?view_resume='.urlencode($application->resume_file))->assertForbidden();
        $this->actingAs($this->user())->get('/uploads/resumes/'.urlencode($application->resume_file))->assertForbidden();
    }

    public function test_upload_categories_reject_unknown_paths_and_scope_portal_images(): void
    {
        $filename = 'event_'.bin2hex(random_bytes(5)).'.png';
        $path = storage_path('app/private/files/uploads/events/'.$filename);
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));
        $this->createdFiles[] = $path;

        $this->actingAs($this->user('employer'))->get('/uploads/events/'.$filename)->assertForbidden();
        $this->actingAs($this->user('alumni'))->get('/uploads/events/'.$filename)->assertOk()->assertHeader('content-type', 'image/png');
        $this->get('/uploads/unknown/'.$filename)->assertNotFound();
        $this->get('/uploads/events/nested/'.$filename)->assertNotFound();
    }

    public function test_deletion_link_does_not_mutate_on_get(): void
    {
        $admin = $this->user('admin');
        $id = DB::table('events')->insertGetId(['title' => 'Do not delete on GET', 'content' => 'Test', 'posted_by' => $admin->id]);
        $this->actingAs($admin)->get('/admin/events_delete.php?id='.$id)->assertOk()->assertSee('Confirm this action');
        $this->assertTrue(DB::table('events')->where('id', $id)->exists());
    }

    public function test_forgot_password_uses_generic_message_and_queued_notification(): void
    {
        Notification::fake();
        $user = $this->user();
        $response = $this->post('/forgot_password.php', ['email' => $user->email]);
        $response->assertSessionHas('status', 'If that address belongs to an account, a password reset link will be sent.');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_token_is_single_use_and_new_password_is_hashed(): void
    {
        $user = $this->user();
        $token = Password::createToken($user);
        $data = ['token' => $token, 'email' => $user->email, 'password' => 'reset-password-123', 'confirm_password' => 'reset-password-123'];
        $this->post('/reset_password.php', $data)->assertRedirect('/');
        $this->assertTrue(Hash::check('reset-password-123', $user->fresh()->password));
        $this->post('/reset_password.php', $data)->assertSessionHasErrors();
    }

    public function test_existing_pages_render_for_their_authorized_roles(): void
    {
        Mail::fake();
        $inventory = json_decode(file_get_contents(base_path('docs/route-inventory.json')), true);
        $users = collect([
            'admin' => $this->user('admin'),
            'alumni' => $this->user('alumni'),
            'employer' => $this->user('employer'),
            'alumni_officer' => $this->user('alumni_officer'),
        ]);
        $jobId = DB::table('jobs')->insertGetId([
            'title' => 'Page inventory job',
            'company' => 'Inventory Company',
            'employer_company' => 'Inventory Company',
            'description' => 'Test',
            'posted_by' => $users['employer']->id,
            'employer_id' => $users['employer']->id,
            'is_open' => 1,
        ]);
        $job = DB::table('jobs')->find($jobId);
        $applicationId = DB::table('applications')->insertGetId(['job_id' => $jobId, 'alumni_id' => $users['alumni']->id, 'status' => 'pending']);
        $application = DB::table('applications')->find($applicationId);
        $eventId = DB::table('events')->insertGetId(['title' => 'Inventory event', 'content' => 'Test', 'posted_by' => $users['admin']->id]);
        $event = DB::table('events')->find($eventId);
        $trainingId = DB::table('trainings')->insertGetId(['title' => 'Inventory training', 'content' => 'Test', 'training_date' => date('Y-m-d'), 'target_course' => 'BSIS', 'posted_by' => $users['admin']->id]);
        $failures = [];
        $this->withoutExceptionHandling();
        foreach ($inventory as $page) {
            $role = $page['role'] === 'authenticated' ? 'alumni' : $page['role'];
            $user = $users[$role] ?? $this->user($role);
            $path = '/'.$page['source'];
            $query = [];
            if (str_contains($path, 'alumni_edit')) {
                $query['id'] = $users['alumni']->id;
            }
            if (str_contains($path, 'events_edit') || str_contains($path, 'events_delete')) {
                $query['id'] = $event->id;
            }
            if (str_contains($path, 'jobs_edit') || str_contains($path, 'job_details')) {
                $query['id'] = $job->id;
            }
            if (str_contains($path, 'trainings_edit')) {
                $query['id'] = $trainingId;
            }
            if ($path === '/admin/applications.php' || str_contains($path, 'jobs_notify') || $path === '/alumni/apply.php') {
                $query['job_id'] = $job->id;
            }
            if (str_contains($path, 'forward_to_company')) {
                $query['app_id'] = $application->id;
            }
            if (str_contains($path, 'interview.php')) {
                $query['application_id'] = $application->id;
                if ($role === 'employer') {
                    $owner = DB::table('jobs')->where('id', $application->job_id)->value('posted_by');
                    $ownerUser = User::find($owner);
                    if ($ownerUser?->role === 'employer') {
                        $user = $ownerUser;
                    }
                }
            }
            try {
                $response = $this->actingAs($user)->get($path.($query ? '?'.http_build_query($query) : ''));
                if ($response->status() >= 500 || str_contains((string) $response->getContent(), 'SQLSTATE[')) {
                    $failures[] = $path.' failed: '.strip_tags(substr((string) $response->getContent(), 0, 350));
                }
            } catch (\Throwable $e) {
                $failures[] = $path.': '.$e->getMessage();
            }
        }
        $this->assertSame([], $failures, implode("\n", $failures));
    }
}
