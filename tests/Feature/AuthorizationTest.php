<?php

namespace Tests\Feature;

use App\Models\AlumniCertificate;
use App\Models\Event;
use App\Models\Interview;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::forceCreate([
            'fullname' => ucfirst($role), 'username' => $role.'_'.uniqid(), 'email' => uniqid().'@example.test',
            'password' => Hash::make('test-password-123'), 'role' => $role, 'is_active' => true, 'status' => 'approved',
        ]);
    }

    public function test_job_and_application_policies_enforce_employer_ownership(): void
    {
        $owner = $this->user('employer');
        $other = $this->user('employer');
        $alumni = $this->user('alumni');
        $job = Job::forceCreate(['title' => 'Policy job', 'company' => 'Company', 'employer_company' => 'Company', 'description' => 'Test', 'posted_by' => $owner->id, 'employer_id' => $owner->id, 'is_open' => true]);
        $application = JobApplication::forceCreate(['job_id' => $job->id, 'alumni_id' => $alumni->id, 'status' => 'pending']);

        $this->assertTrue(Gate::forUser($owner)->allows('update', $job));
        $this->assertFalse(Gate::forUser($other)->allows('update', $job));
        $this->assertTrue(Gate::forUser($owner)->allows('view', $application));
        $this->assertFalse(Gate::forUser($other)->allows('view', $application));
    }

    public function test_event_and_private_file_policies_enforce_ownership(): void
    {
        $admin = $this->user('admin');
        $alumni = $this->user('alumni');
        $otherAlumni = $this->user('alumni');
        $officer = $this->user('alumni_officer');
        $event = Event::forceCreate(['title' => 'Policy event', 'content' => 'Test', 'posted_by' => $officer->id]);

        $this->assertTrue(Gate::forUser($officer)->allows('update', $event));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $event));
        $this->assertTrue(Gate::forUser($alumni)->allows('viewPrivateFile', $alumni));
        $this->assertFalse(Gate::forUser($otherAlumni)->allows('viewPrivateFile', $alumni));
    }

    public function test_interview_policy_enforces_record_ownership(): void
    {
        $admin = $this->user('admin');
        $employer = $this->user('employer');
        $otherEmployer = $this->user('employer');
        $alumni = $this->user('alumni');
        $otherAlumni = $this->user('alumni');
        $interview = Interview::forceCreate([
            'employer_id' => $employer->id,
            'alumni_id' => $alumni->id,
            'interview_date' => now()->addDay()->toDateString(),
            'interview_time' => '09:00:00',
            'location' => 'Campus',
            'status' => 'scheduled',
        ]);
        $this->assertTrue(Gate::forUser($admin)->allows('update', $interview));
        $this->assertTrue(Gate::forUser($employer)->allows('update', $interview));
        $this->assertFalse(Gate::forUser($otherEmployer)->allows('update', $interview));
        $this->assertTrue(Gate::forUser($alumni)->allows('view', $interview));
        $this->assertFalse(Gate::forUser($otherAlumni)->allows('view', $interview));
    }

    public function test_certificate_policy_enforces_alumni_ownership(): void
    {
        $owner = $this->user('alumni');
        $other = $this->user('alumni');
        $admin = $this->user('admin');
        $certificate = new AlumniCertificate(['certificate_name' => 'Test', 'issuer' => 'GradConn']);
        $certificate->user_id = $owner->id;

        $this->assertTrue($owner->can('delete', $certificate));
        $this->assertFalse($other->can('delete', $certificate));
        $this->assertTrue($admin->can('delete', $certificate));
    }
}
