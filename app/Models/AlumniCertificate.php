<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class AlumniCertificate extends Model
{
    public $timestamps = false;

    protected $guarded = ['id', 'user_id', 'created_at'];

    protected function casts(): array
    {
        return ['issue_date' => 'date', 'created_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
