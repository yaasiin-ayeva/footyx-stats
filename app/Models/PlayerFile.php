<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerFile extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'player_id'
    ];

    public function player() {
        return $this->belongsTo(Player::class);
    }

    public function player_file_matches() {
        return $this->hasMany(PlayerFileMatch::class);
    }

    public function get_teams() {
        $home_teams = $this->player_file_matches()->selectRaw('home AS name');
        $away_teams = $this->player_file_matches()->selectRaw('away');
        
        return $home_teams->union($away_teams)->orderBy('name')->get();
    }
}
