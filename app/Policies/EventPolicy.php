<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

final class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'alumni', 'alumni_officer'], true);
    }

    public function update(User $user, Event $event): bool
    {
        return $user->role === 'admin' || ($user->role === 'alumni_officer' && (int) $event->posted_by === (int) $user->id);
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }
}
