<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockUnit extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    protected $casts = [
        'images' => 'array'
    ];

    public function stock(){
        return $this->belongsTo(\App\Models\Stock\Stock::class,'stock_id','id');
    }
    public function unit(){
        return $this->belongsTo(\App\Models\Stock\Unit::class,'unit_id','id');
    }
    public function transactions(){
        return $this->hasMany(\App\Models\Stock\StockTransaction::class,'stock_unit_id','id');
    }
}