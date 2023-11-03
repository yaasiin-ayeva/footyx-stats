<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminFileMatch extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'admin_file_id',
        'league_id',
        'country_id',
        'admin_id',
        
        'year',
        'time',
        'home',
        'away',
        'score',
        
        'date',
        'home_goals',
        'away_goals',
        'winner',
        
        'x1',
        'x2',
        'x3',
        'x4',
        'x5',
        'x6',
        'x7',
        'x8',
        'x9',
        'x10',
        'x11',
        'x12',
        'x13',
        'x14',
        'x15',
        'x16',
        'x17',
        'x18',
        'x19',
        'x20',

        'y1',
        'y2',
        'y3',
        'y4',
        'y5',
        'y6',
        'y7',
        'y8',
        'y9',
        'y10',
        'y11',
        'y12',
        'y13',
        'y14',
        'y15',
        'y16',
        'y17',
        'y18',
        'y19',
        'y20',

        'z1',
        'z2',
        'z3',
        'z4',
        'z5',
        'z6',
        'z7',
        'z8',
        'z9',
        'z10',

        'valid',
    ];

    public function admin_file() {
        return $this->belongsTo(AdminFile::class);
    }

    public function league() {
        return $this->belongsTo(League::class);
    }

    public function country() {
        return $this->belongsTo(Country::class);
    }

    public function admin() {
        return $this->belongsTo(Admin::class);
    }
}
