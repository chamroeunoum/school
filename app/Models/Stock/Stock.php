<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    /**
     * Relationships
     */
    public function store(){
        return $this->belongsTo(\App\Models\Sale\Store::class,'store_id','id');
    }
    public function product(){
        return $this->belongsTo(\App\Models\Product\Product::class,'product_id','id');
    }
    public function attributeVariant(){
        return $this->belongsTo(\App\Models\Product\attributeVariant::class,'attribute_variant_id','id');
    }
    public function stockUnits(){
        return $this->hasMany(\App\Models\Stock\StockUnit::class,'stock_id','id');
    }
    public function stockConventionBreakdown(){
        return $this->hasOne(\App\Models\Stock\UnitConvention::class,'stock_id','id')->where('pid',NULL)->orWhere('pid','<=',0);
    }
    public function stockConventionBuildup(){
        return $this->hasOne(\App\Models\Stock\UnitConvention::class,'stock_id','id')->where('pid','>',0);
    }
}
