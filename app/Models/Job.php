<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    public $timestamps = false;

    protected $guarded = ['id', 'posted_by'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'is_open' => 'boolean', 'created_at' => 'datetime'];
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }

    public function employer()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function poster() { return $this->belongsTo(User::class, 'posted_by'); }
    public function assignedEmployer() { return $this->belongsTo(User::class, 'employer_id'); }
    public function interviews() { return $this->hasMany(Interview::class); }
}
