<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class PostComment extends Model
{
    public $timestamps = false;
    protected $guarded = ['id', 'user_id', 'created_at'];
    protected function casts(): array { return ['created_at' => 'datetime']; }
    public function author() { return $this->belongsTo(User::class, 'user_id'); }
    public function parent() { return $this->belongsTo(self::class, 'parent_comment_id'); }
    public function replies() { return $this->hasMany(self::class, 'parent_comment_id'); }
}
