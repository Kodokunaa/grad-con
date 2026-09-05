<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Training extends Model
{
    public $timestamps = false;

    protected $guarded = ['id', 'posted_by', 'created_at'];

    protected function casts(): array
    {
        return ['training_date' => 'date', 'created_at' => 'datetime'];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class, 'post_id')->where('post_type', 'training');
    }

    public function reactions()
    {
        return $this->hasMany(PostReaction::class, 'post_id')->where('post_type', 'training');
    }
}
