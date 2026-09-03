<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $table = 'applications';

    public $timestamps = false;

    protected $guarded = ['id', 'alumni_id', 'status'];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function alumni()
    {
        return $this->belongsTo(User::class, 'alumni_id');
    }
}
