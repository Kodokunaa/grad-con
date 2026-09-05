<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public $timestamps = false;

    protected $fillable = ['fullname', 'username', 'email', 'password', 'course', 'batch_year'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'is_active' => 'boolean', 'birthdate' => 'date', 'age' => 'integer', 'created_at' => 'datetime'];
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'alumni_id');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'posted_by');
    }

    public function assignedJobs()
    {
        return $this->hasMany(Job::class, 'employer_id');
    }

    public function certificates()
    {
        return $this->hasMany(AlumniCertificate::class);
    }

    public function degrees()
    {
        return $this->hasMany(AlumniDegree::class);
    }

    public function education()
    {
        return $this->hasMany(AlumniEducation::class);
    }

    public function employmentHistory()
    {
        return $this->hasMany(EmploymentHistory::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'posted_by');
    }

    public function trainings()
    {
        return $this->hasMany(Training::class, 'posted_by');
    }

    public function receivedOffers()
    {
        return $this->hasMany(JobOffer::class, 'alumni_id');
    }

    public function sentOffers()
    {
        return $this->hasMany(JobOffer::class, 'employer_id');
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class, 'alumni_id');
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }

    public function reactions()
    {
        return $this->hasMany(PostReaction::class);
    }

    public function receivedPostNotifications()
    {
        return $this->hasMany(PostNotification::class, 'recipient_user_id');
    }

    public function sentPostNotifications()
    {
        return $this->hasMany(PostNotification::class, 'sender_user_id');
    }

    public function securityLogs()
    {
        return $this->hasMany(SecurityLog::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }
}
