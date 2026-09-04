<?php

namespace App\Policies;

use App\Models\EmploymentHistory;
use App\Models\User;

final class EmploymentHistoryPolicy
{
    public function delete(User $user, EmploymentHistory $employment): bool
    {
        return $user->role === 'alumni' && (int) $employment->user_id === (int) $user->id;
    }
}
