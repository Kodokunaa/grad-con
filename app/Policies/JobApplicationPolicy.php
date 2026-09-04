<?php

namespace App\Policies;

use App\Models\JobApplication;
use App\Models\User;

final class JobApplicationPolicy
{
    public function view(User $user, JobApplication $application): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'alumni' && (int) $application->alumni_id === (int) $user->id)
            || ($user->role === 'employer' && ((int) $application->job?->posted_by === (int) $user->id || (int) $application->job?->employer_id === (int) $user->id));
    }

    public function update(User $user, JobApplication $application): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'alumni' && (int) $application->alumni_id === (int) $user->id)
            || ($user->role === 'employer' && ((int) $application->job?->posted_by === (int) $user->id || (int) $application->job?->employer_id === (int) $user->id));
    }

    public function delete(User $user, JobApplication $application): bool
    {
        return $user->role === 'admin' || ($user->role === 'alumni' && (int) $application->alumni_id === (int) $user->id);
    }
}
