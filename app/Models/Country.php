<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'code',
    ];

    public function leagues() {
        return $this->hasMany(League::class);
    }

    public function admin_files() {
        return $this->hasMany(AdminFile::class);
    }

    public function admin_file_matches() {
        return $this->hasMany(AdminFileMatch::class);
    }
}
