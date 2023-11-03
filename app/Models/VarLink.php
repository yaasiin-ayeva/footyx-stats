<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VarLink extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'var_group_id',
        'var'
    ];

    public function var_group() {
        return $this->belongsTo(VarGroup::class);
    }
}
