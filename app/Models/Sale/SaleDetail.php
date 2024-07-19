<?php

namespace App\Models\Sale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleDetail extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    public function sale(){
        return $this->belongsTo(\App\Models\Sale\Sale::class,'sale_id','id');
    }
    public function stockUnit(){
        return $this->belongsTo(\App\Models\Stock\StockUnit::class,'stock_unit_id','id');
    }
    public function getDiscountAmount(){
        // Get discount
        // Discount base on discount amount or percentage
    }
}
