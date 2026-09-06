<?php

namespace Tests\Feature;

use App\Mail\AlumniAccountApprovedMail;
use App\Mail\DeliveryTestMail;
use App\Models\JobApplication;
use App\Models\User;
use App\Notifications\ResetPassword;
use App\Support\PrivateUploads;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;
use Tests\TestCase;

final class MigrationTest extends TestCase
{
    use DatabaseTransactions;

    private array $createdFiles = [];

    public function test_transactional_email_uses_the_orange_gradconn_theme(): void
    {
        $html = (new AlumniAccountApprovedMail($this->user('alumni')))->render();

        $this->assertStringContainsString('GradConn', $html);
        $this->assertStringContainsString('#f97316', $html);
        $this->assertStringContainsString('linear-gradient(135deg,#f97316 0%,#ea580c 100%)', $html);
        $this->assertStringContainsString('Alumni Account Update', $html);
        $this->assertStringContainsString('Account approved', $html);
    }

    public function test_admin_dashboard_reuses_its_warm_query_snapshot(): void
    {
        $admin = $this->user('admin');
        Cache::forget('dashboard.admin.metrics.v1');
        Cache::forget('sidebar.pending-alumni.v1');

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $queries = DB::getQueryLog();
        $this->assertCount(1, $queries);
        $this->assertStringContainsString('from `users`', $queries[0]['query']);
        DB::disableQueryLog();
        Cache::forget('dashboard.admin.metrics.v1');
        Cache::forget('sidebar.pending-alumni.v1');
    }

    public function test_delivery_command_sends_the_branded_html_template(): void
    {
        Mail::fake();
        config(['mail.default' => 'brevo']);

        $this->artisan('gradconn:test-mail recipient@example.com')->assertSuccessful();

        Mail::assertSent(DeliveryTestMail::class, fn (DeliveryTestMail $mail) => str_contains($mail->render(), 'Email Delivery Confirmed')
            && str_contains($mail->render(), '#f97316'));
    }

    public function test_shared_html_escaping_helper_preserves_legacy_behavior(): void
    {
        $this->assertSame('&lt;tag title=&quot;test&quot;&gt;&#039;&amp;', e('<tag title="test">\'&'));
        $this->assertSame('', e(null));
    }

    public function test_portable_installation_defaults_and_readiness_check(): void
    {
        $this->assertSame('GradConn', config('app.name'));
        $this->assertSame('Asia/Manila', config('app.timezone'));
        $this->artisan('gradconn:check')->assertSuccessful();
    }

    public function test_resend_readiness_check_requires_key_and_verified_domain_sender(): void
    {
        config()->set('mail.default', 'resend');
        config()->set('services.resend.key', null);
        config()->set('mail.from.address', 'sender@gmail.com');
        $this->artisan('gradconn:check --mail')
            ->expectsOutputToContain('RESEND_API_KEY is missing.')
            ->expectsOutputToContain('verified domain')
            ->assertFailed();

        config()->set('services.resend.key', 're_test_key');
        config()->set('mail.from.address', 'notifications@updates.example.test');
        $this->artisan('gradconn:check --mail')->assertSuccessful();
    }

    public function test_brevo_readiness_check_requires_the_provider_smtp_login(): void
    {
        config()->set('mail.default', 'smtp');
        config()->set('mail.mailers.smtp', [
            'host' => 'smtp-relay.brevo.com',
            'port' => 587,
            'username' => 'account@gmail.com',
            'password' => 'smtp-key',
        ]);
        config()->set('mail.from.address', 'sender@gmail.com');
        $this->artisan('gradconn:check --mail')
            ->expectsOutputToContain('@smtp-brevo.com')
            ->assertFailed();

        config()->set('mail.mailers.smtp.username', 'account@smtp-brevo.com');
        $this->artisan('gradconn:check --mail')->assertSuccessful();
    }

    public function test_brevo_api_mailer_and_private_cloud_uploads_are_configurable(): void
    {
        config()->set('mail.default', 'brevo');
        config()->set('mail.mailers.brevo.key', 'test-api-key');
        config()->set('services.brevo.key', 'test-api-key');
        config()->set('mail.from.address', 'verified@example.test');

        $this->artisan('gradconn:check --mail')->assertSuccessful();
        $this->assertInstanceOf(BrevoApiTransport::class, Mail::mailer('brevo')->getSymfonyTransport());

        config()->set('filesystems.uploads_disk', 's3');
        Storage::fake('s3');
        $upload = UploadedFile::fake()->create('resume.pdf', 20, 'application/pdf');

        $this->assertTrue(PrivateUploads::store($upload, 'resumes', 'candidate.pdf'));
        Storage::disk('s3')->assertExists('files/uploads/resumes/candidate.pdf');
        $this->assertTrue(PrivateUploads::delete('resumes', 'candidate.pdf'));
        Storage::disk('s3')->assertMissing('files/uploads/resumes/candidate.pdf');
    }

    public function test_render_container_keeps_secrets_external_and_runs_safe_startup_tasks(): void
    {
        $dockerfile = file_get_contents(base_path('Dockerfile'));
        $startup = file_get_contents(base_path('docker/start.sh'));
        $blueprint = file_get_contents(base_path('render.yaml'));
        $apache = file_get_contents(base_path('docker/apache-vhost.conf'));
        $php = file_get_contents(base_path('docker/php-production.ini'));

        $this->assertStringContainsString('DocumentRoot /var/www/html/public', $apache);
        $this->assertStringContainsString('AddOutputFilterByType DEFLATE', $apache);
        $this->assertStringContainsString('max-age=31536000, immutable', $apache);
        $this->assertStringContainsString('opcache.enable=1', $php);
        $this->assertStringContainsString('opcache.validate_timestamps=0', $php);
        $this->assertStringContainsString('php artisan migrate --force', $startup);
        $this->assertStringContainsString('php artisan gradconn:check --database --mail', $startup);
        $this->assertStringContainsString('php artisan db:seed --class=AdminSeeder --force', $startup);
        $this->assertStringContainsString('chown www-data:www-data storage/certs/aiven-ca.pem', $startup);
        $this->assertStringContainsString('chmod 640 storage/certs/aiven-ca.pem', $startup);
        $this->assertStringContainsString('php artisan config:clear', $startup);
        $this->assertStringContainsString('php artisan queue:work', $startup);
        $this->assertStringNotContainsString('php artisan optimize:clear', $startup);
        $this->assertStringContainsString('if [ -n "${ADMIN_SEED_PASSWORD:-}" ]', $startup);
        $this->assertStringContainsString('composer install --no-dev', $dockerfile);
        $this->assertStringContainsString('FROM php:8.3-cli AS vendor', $dockerfile);
        $this->assertStringContainsString('docker-php-ext-install bcmath curl dom intl mbstring pdo_mysql xml zip', $dockerfile);
        $this->assertStringContainsString('BREVO_API_KEY', $blueprint);
        $this->assertMatchesRegularExpression('/key: CACHE_STORE\s+value: file/', $blueprint);
        $this->assertMatchesRegularExpression('/key: SESSION_DRIVER\s+value: cookie/', $blueprint);
        $this->assertMatchesRegularExpression('/key: QUEUE_CONNECTION\s+value: database/', $blueprint);
        $this->assertStringContainsString('MaxRequestWorkers 16', $dockerfile);
        $this->assertMatchesRegularExpression('/key: DB_PORT\s+sync: false/', $blueprint);
        $this->assertStringNotContainsString('api-key-', $blueprint);
        $this->assertStringNotContainsString('ADD COLUMN IF NOT EXISTS', file_get_contents(database_path('schema/gradconn.json')));
        $baselineMigration = file_get_contents(database_path('migrations/2026_09_04_000001_preserve_gradconn_schema.php'));
        $infrastructureMigration = file_get_contents(database_path('migrations/2026_09_04_000002_add_laravel_infrastructure.php'));
        $this->assertStringContainsString("unsignedTinyInteger('id')->primary()", $baselineMigration);
        $this->assertStringContainsString("Schema::create('cache'", $infrastructureMigration);
        $this->assertStringContainsString("string('key')->primary()", $infrastructureMigration);
    }

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
        $this->post('/register', ['fullname' => 'New Graduate', 'student_id' => 'migration-registration', 'email' => 'registration@example.test', 'course' => 'BSIS', 'batch_year' => '2025', 'password' => 'new-password-123', 'confirm_password' => 'new-password-123'])->assertRedirect('/register');
        $user = User::where('username', 'migration-registration')->firstOrFail();
        $this->assertTrue(Hash::check('new-password-123', $user->password));
        $this->assertFalse($user->is_active);
        $this->assertSame('pending', $user->status);
        $this->post('/', ['student_id' => $user->username, 'password' => 'new-password-123'])->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_validation_redirects_retain_safe_form_input(): void
    {
        $this->from('/register')->post('/register', [
            'fullname' => 'Remembered Graduate',
            'student_id' => '',
            'email' => 'remembered@example.test',
            'course' => 'BSIS',
            'batch_year' => '2025',
            'password' => 'new-password-123',
            'confirm_password' => 'new-password-123',
        ])->assertRedirect('/register');

        $this->get('/register')
            ->assertSee('Remembered Graduate')
            ->assertSee('remembered@example.test');
    }

    public function test_login_displays_flash_status_messages(): void
    {
        $response = $this->withSession(['status' => 'Password reset successful. Please sign in.'])
            ->get('/')
            ->assertSee('Password reset successful. Please sign in.');

        $html = $response->getContent();
        $this->assertSame(1, substr_count($html, 'name="csrf-token"'));
        $this->assertSame(1, substr_count($html, 'js/request-security.js'));
        $this->assertSame(1, substr_count($html, 'name="_token"'));
    }

    public function test_logout_uses_an_in_page_modal_and_remains_post_only(): void
    {
        $admin = $this->user('admin');

        $response = $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('id="logoutLightbox"', false)
            ->assertSee('data-logout-trigger', false)
            ->assertSee('Log out of GradConn?');

        $this->assertSame(1, substr_count($response->getContent(), 'id="logoutLightbox"'));
        $this->assertSame(1, substr_count($response->getContent(), 'js/request-security.js'));

        $this->get('/auth/logout')->assertRedirect('/');
        $this->assertAuthenticatedAs($admin);
        $this->post('/auth/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_logout_form_stays_https_behind_a_trusted_proxy(): void
    {
        $alumni = $this->user('alumni');

        $this->actingAs($alumni)
            ->withHeader('X-Forwarded-Proto', 'https')
            ->get('/alumni/feed')
            ->assertSuccessful()
            ->assertSee('action="/auth/logout"', false)
            ->assertSee('href="/css/authenticated.css"', false)
            ->assertSee('href="/css/logout-modal.css"', false)
            ->assertSee('src="/js/logout-modal.js"', false)
            ->assertSee('data-logout-trigger', false)
            ->assertSee('class="empty-state"', false)
            ->assertDontSee('action="http://', false);
    }

    public function test_public_auth_links_and_legacy_page_aliases_route_correctly(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('src="/ccc3d.png"', false)
            ->assertSee('rel="icon" type="image/png" href="/ccc3d.png?v=2"', false)
            ->assertSee(route('password.request'), false)
            ->assertSee(route('register'), false);

        $this->get('/register')->assertOk()->assertSee(route('login'), false)->assertSee('/ccc3d.png?v=2', false);
        $this->get('/reset-password')->assertOk()->assertSee(route('login'), false)->assertSee('/ccc3d.png?v=2', false);
        $this->get('/auth/admin_login.php')->assertRedirect('/', 301);
        $this->get('/alumni/feed.php?from=bookmark')->assertRedirect('/alumni/feed?from=bookmark', 301);

        $admin = $this->user('admin');
        $this->actingAs($admin)
            ->get('/admin/employer_list.php')
            ->assertRedirect('/admin/create_employer', 301);

        $employer = $this->user('employer');
        $this->actingAs($employer)
            ->get('/employer/my_jobs.php')
            ->assertRedirect('/employer/posted_job', 301);
        $this->get('/employer/job_list.php')->assertRedirect('/employer/posted_job', 301);
        $this->get('/employer/jobl_list.php')->assertRedirect('/employer/posted_job', 301);
    }

    public function test_read_only_pages_and_compatibility_redirects_reject_post_requests(): void
    {
        $cases = [
            'admin' => ['/admin/dashboard', '/admin/alumni_report', '/admin/graduates_list', '/admin/graduates_report', '/admin/graduates_stats', '/admin/jobs_list', '/admin/offers_history', '/admin/reports', '/admin/employer-list'],
            'alumni' => ['/alumni/dashboard', '/alumni/jobs'],
            'alumni_officer' => ['/alumni_officer/dashboard', '/archive'],
            'employer' => ['/employer/dashboard', '/employer/my-jobs', '/employer/job-list'],
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
        $this->post('/auth/login.php', ['username' => $user->username, 'password' => 'test-password-123'])->assertRedirect('/employer/dashboard');
        $this->assertAuthenticatedAs($user);
        $user->is_active = false;
        $user->save();
        $this->get('/employer/dashboard')->assertRedirect('/');
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
        $this->actingAs($user)->put('/profile/password', ['change_password_page' => 1, 'old_password' => 'test-password-123', 'new_password' => 'replacement-password', 'confirm_password' => 'replacement-password'])->assertRedirect(route('alumni.change_password'));
        $this->assertTrue(Hash::check('replacement-password', $user->fresh()->password));
        $this->assertDatabaseMissing('sessions', ['id' => 'another-device-session']);
        $this->assertDatabaseHas('security_logs', ['user_id' => $user->id, 'action' => 'PASSWORD_CHANGED']);
    }

    public function test_admin_can_change_password_from_profile_and_log_in_again(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)->put('/profile/password', [
            'old_password' => 'test-password-123',
            'new_password' => 'replacement-admin-password',
            'confirm_password' => 'replacement-admin-password',
        ])->assertRedirect('/profile?tab=security');

        $this->assertTrue(Hash::check('replacement-admin-password', $admin->fresh()->password));
        Auth::logout();

        $this->post('/', [
            'username' => $admin->username,
            'password' => 'replacement-admin-password',
        ])->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_roles_cannot_access_each_others_portals(): void
    {
        foreach (['admin', 'alumni', 'employer', 'alumni_officer'] as $role) {
            $this->actingAs($this->user($role));
            foreach (array_diff(['admin', 'alumni', 'employer', 'alumni_officer'], [$role]) as $other) {
                $this->get('/'.$other.'/dashboard')->assertForbidden();
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
        $this->actingAs($this->user('employer'))->get('/employer/applications?view_resume='.urlencode($application->resume_file))->assertForbidden();
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
        $this->actingAs($admin)->get('/admin/events_delete?id='.$id)->assertRedirect(route('admin.events_list'));
        $this->assertTrue(DB::table('events')->where('id', $id)->exists());
    }

    public function test_forgot_password_uses_generic_message_and_queued_notification(): void
    {
        Notification::fake();
        $user = $this->user();
        $response = $this->post('/forgot-password', ['email' => $user->email]);
        $response->assertSessionHas('status', 'If that address belongs to an account, a password reset link will be sent.');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_token_is_single_use_and_new_password_is_hashed(): void
    {
        $user = $this->user();
        $token = Password::createToken($user);
        $data = ['token' => $token, 'email' => $user->email, 'password' => 'reset-password-123', 'confirm_password' => 'reset-password-123'];
        $this->post('/reset-password', $data)->assertRedirect('/');
        $this->assertTrue(Hash::check('reset-password-123', $user->fresh()->password));
        $this->post('/reset-password', $data)->assertSessionHasErrors();
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
        $officerEventId = DB::table('events')->insertGetId(['title' => 'Officer inventory event', 'content' => 'Test', 'posted_by' => $users['alumni_officer']->id]);
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
                $query['id'] = $role === 'alumni_officer' ? $officerEventId : $event->id;
            }
            if (str_contains($path, 'jobs_edit') || str_contains($path, 'job_details')) {
                $query['id'] = $job->id;
            }
            if ($path === '/admin/applications' || str_contains($path, 'jobs_notify') || $path === '/alumni/apply') {
                $query['job_id'] = $job->id;
            }
            if (str_contains($path, 'forward_to_company')) {
                $query['app_id'] = $application->id;
            }
            if (str_contains($path, 'interview')) {
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
                } elseif ($response->isSuccessful() && str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
                    $content = (string) $response->getContent();
                    if (! str_contains($content, 'class="sidebar gradconn-sidebar"')) {
                        $failures[] = $path.' is missing the shared role sidebar.';
                    }
                    if (! str_contains($content, 'class="app-header gradconn-navbar"')) {
                        $failures[] = $path.' is missing the shared authenticated navbar.';
                    }
                    if (! str_contains($content, 'rel="icon" type="image/png" href="/ccc3d.png?v=2"')) {
                        $failures[] = $path.' is missing the GradConn favicon.';
                    }
                }
            } catch (\Throwable $e) {
                $failures[] = $path.': '.$e->getMessage();
            }
        }
        $this->assertSame([], $failures, implode("\n", $failures));
    }

    public function test_every_role_uses_the_same_sidebar_shell(): void
    {
        $pages = [
            'admin' => ['/admin/dashboard', 'Admin Panel', 'Pending Accounts'],
            'alumni' => ['/alumni/feed', 'Alumni Panel', 'Community Feed'],
            'employer' => ['/employer/dashboard', 'Employer Panel', 'Posted Jobs'],
            'alumni_officer' => ['/alumni_officer/dashboard', 'Alumni Officer Panel', 'Community Posts'],
        ];

        foreach ($pages as $role => [$path, $panel, $link]) {
            $response = $this->actingAs($this->user($role))->get($path)->assertSuccessful();
            $response->assertSee('class="sidebar gradconn-sidebar"', false)
                ->assertSee('href="/css/sidebar.css"', false)
                ->assertSee('src="/js/sidebar.js"', false)
                ->assertSee('class="app-header gradconn-navbar"', false)
                ->assertSee('href="/css/navbar.css"', false)
                ->assertSee($panel)
                ->assertSee($link);
            $this->assertSame(1, substr_count($response->getContent(), 'class="sidebar gradconn-sidebar"'));
        }
    }

    public function test_alumni_directory_lands_on_the_community_feed(): void
    {
        $alumni = $this->user('alumni');

        $this->actingAs($alumni)->get('/')->assertRedirect('/alumni/feed');
        $this->get('/alumni/dashboard')->assertRedirect(route('alumni.feed'));
        $this->get('/alumni/feed')
            ->assertOk()
            ->assertSee('Community Feed')
            ->assertDontSee('>Dashboard<', false);
    }

    public function test_training_program_is_removed_from_the_application(): void
    {
        $admin = $this->user('admin');

        $this->assertFalse(Schema::hasTable('trainings'));
        $this->assertFalse(Schema::hasColumn('users', 'trainings'));
        $this->assertFalse(Schema::hasColumn('applications', 'applicant_trainings'));
        $this->actingAs($admin)->get('/admin/trainings_list')->assertNotFound();
        $this->post('/trainings')->assertNotFound();
        $this->get('/admin/dashboard')->assertOk()->assertDontSee('Training Programs');
    }
}
