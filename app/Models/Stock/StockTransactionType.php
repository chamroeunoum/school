<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransactionType extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    public function stockTransactions(){
        return $this->hasMany(\App\Models\Stock\StockTransaction::class,'transaction_type_id','id');
    }
}
