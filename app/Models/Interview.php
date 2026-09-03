<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Interview extends Model
{
    public $timestamps = false;
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected function casts(): array { return ['interview_date' => 'date', 'email_sent' => 'boolean', 'created_at' => 'datetime', 'updated_at' => 'datetime']; }
    public function application() { return $this->belongsTo(JobApplication::class, 'application_id'); }
    public function offer() { return $this->belongsTo(JobOffer::class, 'offer_id'); }
    public function employer() { return $this->belongsTo(User::class, 'employer_id'); }
    public function administrator() { return $this->belongsTo(User::class, 'admin_id'); }
    public function alumni() { return $this->belongsTo(User::class, 'alumni_id'); }
    public function job() { return $this->belongsTo(Job::class); }
}
