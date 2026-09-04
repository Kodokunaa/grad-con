<?php

namespace App\Policies;

use App\Models\User;

final class UserPolicy
{
    public function viewPrivateFile(User $actor, User $owner): bool
    {
        return $actor->role === 'admin' || $actor->is($owner);
    }

    public function update(User $actor, User $subject): bool
    {
        return $actor->role === 'admin' || $actor->is($subject);
    }

    public function delete(User $actor, User $subject): bool
    {
        return $actor->role === 'admin' && $subject->role !== 'admin';
    }
}
