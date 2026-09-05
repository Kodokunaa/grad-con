<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $table = 'applications';

    public $timestamps = false;

    protected $guarded = ['id', 'alumni_id', 'status'];

    protected function casts(): array
    {
        return ['applicant_birthdate' => 'date', 'applicant_age' => 'integer', 'cancelled_at' => 'datetime', 'created_at' => 'datetime'];
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function alumni()
    {
        return $this->belongsTo(User::class, 'alumni_id');
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class, 'application_id');
    }
}
