<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class AlumniEducation extends Model
{
    protected $table = 'alumni_education';

    public $timestamps = false;

    protected $guarded = ['id', 'user_id', 'created_at'];

    protected function casts(): array
    {
        return ['start_year' => 'integer', 'end_year' => 'integer', 'created_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
