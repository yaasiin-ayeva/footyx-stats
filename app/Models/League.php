<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'country_id',
    ];

    public function country() {
        return $this->belongsTo(Country::class);
    }

    public function admin_files() {
        return $this->hasMany(AdminFile::class);
    }

    public function admin_file_matches() {
        return $this->hasMany(AdminFileMatch::class);
    }
}
