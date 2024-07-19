<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    protected $casts = [
        'images' => 'array'
    ];
    public function stock(){
        return $this->hasMany(\App\Models\Stock\Stock::class,'product_id','id');
    }
    public function tags(){
        return $this->belongsTo(\App\Models\Tag::class,'tag_id','id');
    }
}
