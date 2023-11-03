<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerFileMatch extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'player_file_id',
        'player_id',
        
        'year',
        'time',
        'home',
        'away',
        'score',
        
        'date',
        'home_goals',
        'away_goals',
        'winner',
    ];

    public function player_file() {
        return $this->belongsTo(PlayerFile::class);
    }
}
