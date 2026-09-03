<?php

namespace App\Models;

use App\Enums\OfferStatus;
use Illuminate\Database\Eloquent\Model;

final class JobOffer extends Model
{
    protected $table = 'job_offers';
    public $timestamps = false;
    protected $guarded = ['id', 'employer_id', 'alumni_id', 'offer_token', 'created_at', 'updated_at'];
    protected function casts(): array { return ['status' => OfferStatus::class, 'accepted_at' => 'datetime', 'declined_at' => 'datetime', 'expires_at' => 'datetime', 'created_at' => 'datetime', 'updated_at' => 'datetime']; }
    public function employer() { return $this->belongsTo(User::class, 'employer_id'); }
    public function alumni() { return $this->belongsTo(User::class, 'alumni_id'); }
    public function interviews() { return $this->hasMany(Interview::class, 'offer_id'); }
}
