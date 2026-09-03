<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    public $timestamps = false;

    protected $guarded = ['id', 'posted_by'];

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }

    public function employer()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
