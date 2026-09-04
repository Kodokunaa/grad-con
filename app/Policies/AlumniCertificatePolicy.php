<?php

namespace App\Policies;

use App\Models\AlumniCertificate;
use App\Models\User;

final class AlumniCertificatePolicy
{
    public function delete(User $user, AlumniCertificate $certificate): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'alumni' && (int) $certificate->user_id === (int) $user->id);
    }
}
