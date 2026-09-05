<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Event extends Model
{
    public $timestamps = false;

    protected $guarded = ['id', 'posted_by', 'created_at'];

    protected function casts(): array
    {
        return ['post_start_date' => 'datetime', 'post_end_date' => 'datetime', 'is_archived' => 'boolean', 'archived_at' => 'datetime', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class, 'post_id')->where('post_type', 'event');
    }

    public function reactions()
    {
        return $this->hasMany(PostReaction::class, 'post_id')->where('post_type', 'event');
    }
}
