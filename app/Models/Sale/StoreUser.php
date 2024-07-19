<?php

namespace App\Models\Sale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreUser extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    public function store(){
        return $this->belongsTo(\App\Models\Sale\Store::class,'store_id','id');
    }
    public function user(){
        return $this->belongsTo(\App\Models\User::class,'user_id','id');
    }
}
