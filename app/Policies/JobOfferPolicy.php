<?php

namespace App\Policies;

use App\Models\JobOffer;
use App\Models\User;

final class JobOfferPolicy
{
    public function view(User $user, JobOffer $offer): bool
    {
        return $user->role === 'admin' || (int) $offer->employer_id === (int) $user->id || (int) $offer->alumni_id === (int) $user->id;
    }

    public function update(User $user, JobOffer $offer): bool
    {
        return $user->role === 'admin' || ($user->role === 'employer' && (int) $offer->employer_id === (int) $user->id);
    }

    public function respond(User $user, JobOffer $offer): bool
    {
        return $user->role === 'alumni' && (int) $offer->alumni_id === (int) $user->id;
    }

    public function delete(User $user, JobOffer $offer): bool
    {
        return $this->update($user, $offer);
    }
}
