<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitConvention extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    /**
     * Relationships
     */
    public function stock(){
        return $this->belongsTo(\App\Models\Stock\Stock::class,'stock_id','id');
    }
    public function fromUnit(){
        return $this->belongsTo(\App\Models\Stock\Unit::class,'from_stock_unit_id','id');
    }
    public function toUnit(){
        return $this->belongsTo(\App\Models\Stock\Unit::class,'to_stock_unit_id','id');
    }
}
