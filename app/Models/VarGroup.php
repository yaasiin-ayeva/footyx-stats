<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VarGroup extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'admin_id'
    ];

    public function var_links()
    {
        return $this->hasMany(VarLink::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
