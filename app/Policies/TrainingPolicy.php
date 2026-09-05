<?php

namespace App\Policies;

use App\Models\Training;
use App\Models\User;

final class TrainingPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'alumni', 'alumni_officer'], true);
    }

    public function update(User $user, Training $training): bool
    {
        return $user->role === 'admin' || ($user->role === 'alumni_officer' && (int) $training->posted_by === (int) $user->id);
    }

    public function delete(User $user, Training $training): bool
    {
        return $this->update($user, $training);
    }
}
