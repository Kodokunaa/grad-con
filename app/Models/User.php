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
        return ['password' => 'hashed', 'is_active' => 'boolean'];
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'alumni_id');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'posted_by');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }
}
