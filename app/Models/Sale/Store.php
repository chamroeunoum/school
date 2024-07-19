<?php

namespace App\Models\Sale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    protected $casts = [
        'images' => 'array'
    ];

    public function sales(){
        return $this->hasMany(\App\Models\Sale\Sale::class,'store_id','id');
    }
    public function users(){
        return $this->belongsToMany(\App\Models\User::class,'store_users','store_id','user_id');
    }
    public function owners(){
        return $this->belongsToMany(\App\Models\User::class,'store_owners','store_id','user_id');
      }
    public function stocks(){
        return $this->hasMany(\App\Models\Stock\Stock::class,'store_id','id');
    }
    public function products(){
        $this->stocks->get()->map(function($stock){
            return [
                'product' => $stock->product ,
                'attributeVariant' -> $stock->attributeVariant
            ];
        });
    }
}
