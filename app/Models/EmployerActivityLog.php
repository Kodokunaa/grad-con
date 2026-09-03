<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class EmployerActivityLog extends Model
{
    public $timestamps = false;
    protected $guarded = ['id', 'employer_id', 'created_at'];
    protected function casts(): array { return ['result_count' => 'integer', 'created_at' => 'datetime']; }
    public function employer() { return $this->belongsTo(User::class, 'employer_id'); }
    public function alumni() { return $this->belongsTo(User::class, 'alumni_id'); }
    public function offer() { return $this->belongsTo(JobOffer::class, 'offer_id'); }
}
