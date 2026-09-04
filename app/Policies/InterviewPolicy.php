<?php

namespace App\Policies;

use App\Models\Interview;
use App\Models\User;

final class InterviewPolicy
{
    public function view(User $user, Interview $interview): bool { return $user->role === 'admin' || (int) $interview->employer_id === (int) $user->id || (int) $interview->alumni_id === (int) $user->id; }
    public function update(User $user, Interview $interview): bool { return $user->role === 'admin' || ($user->role === 'employer' && (int) $interview->employer_id === (int) $user->id); }
}
