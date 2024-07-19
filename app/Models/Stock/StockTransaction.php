<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransaction extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    public function stock(){
        return $this->belongsTo(\App\Models\Stock\Stock::class,'stock_id','id');
    }
    public function type(){
        return $this->belongsTo(\App\Models\Stock\StockTransactionType::class,'transaction_type_id','id');
    }
    public function user(){
        return $this->belongsTo(\App\Models\User::class,'user_id','id');
    }
    public function unit(){
        return $this->belongsTo(\App\Models\Stock\Unit::class,'unit_id','id');
    }
}
