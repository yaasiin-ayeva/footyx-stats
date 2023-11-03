<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'link',
        'icon',
        'seen_at',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
