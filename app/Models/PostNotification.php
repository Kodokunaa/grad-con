<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class PostNotification extends Model
{
    public $timestamps = false;
    protected $guarded = ['id', 'recipient_user_id', 'sender_user_id', 'created_at'];
    protected function casts(): array { return ['is_read' => 'boolean', 'created_at' => 'datetime']; }
    public function recipient() { return $this->belongsTo(User::class, 'recipient_user_id'); }
    public function sender() { return $this->belongsTo(User::class, 'sender_user_id'); }
}
