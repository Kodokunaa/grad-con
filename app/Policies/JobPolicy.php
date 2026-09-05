<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

final class JobPolicy
{
    public function view(User $user, Job $job): bool
    {
        return $user->role === 'admin' || $user->role === 'alumni'
            || ($user->role === 'employer' && ((int) $job->posted_by === (int) $user->id || (int) $job->employer_id === (int) $user->id));
    }

    public function update(User $user, Job $job): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'employer' && ((int) $job->posted_by === (int) $user->id || (int) $job->employer_id === (int) $user->id));
    }

    public function delete(User $user, Job $job): bool
    {
        return $this->update($user, $job);
    }
}
