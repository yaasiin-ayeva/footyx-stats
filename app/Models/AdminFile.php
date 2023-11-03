<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminFile extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        
        'league_id',
        'country_id',
        'admin_id'
    ];

    public function league() {
        return $this->belongsTo(League::class);
    }

    public function country() {
        return $this->belongsTo(Country::class);
    }

    public function admin() {
        return $this->belongsTo(Admin::class);
    }

    public function admin_file_matches() {
        return $this->hasMany(AdminFileMatch::class);
    }
}
