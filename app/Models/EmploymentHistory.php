<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class EmploymentHistory extends Model
{
    protected $table = 'employment_history';
    public $timestamps = false;
    protected $guarded = ['id', 'user_id', 'created_at'];
    protected function casts(): array { return ['start_date' => 'date', 'end_date' => 'date', 'created_at' => 'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
}
