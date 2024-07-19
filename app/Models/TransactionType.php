<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionType extends Model
{
    use SoftDeletes;
    protected $casts = [
        'name' => 'string' ,
        'icon' => 'string' ,
        'color' => 'string' ,
        'active' => 'int'
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at','active'];

    public function setNameAttribute($val){
        $this->attributes['name'] = (string) $val;
    }
    public function setIconAttribute($val){
        $this->attributes['icon'] = (string) $val;
    }
    public function setColorAttribute($val){
        $this->attributes['color'] = (string) $val;
    }
    public function setActiveAttribute($val){
        $this->attributes['active'] = (int) $val;
    }
}
