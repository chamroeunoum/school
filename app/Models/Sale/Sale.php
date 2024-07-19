<?php

namespace App\Models\Sale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    // Sale transactions
    public function transactions(){
        return $this->hasMany(\App\Models\Sale\SaleDetail::class,'sale_id','id');
    }
    public function getTotalAmount(){
        return $this->transactions()->sum('amount');
    }
    public function getTotalDiscount(){
        $totalDiscount = 0 ;
        foreach( $this->transactions() AS $index => $transaction ){
            $totalDiscount += $transaction->getTotalDiscount() ;
        }
        return $totalDiscount ;
    }
    public function getAmountAfterDiscount(){
        return $this->getTotalAmount() - $this->getTotalDiscount() ;
    }
    public function getVatAmount(){
        return $this->getAmountAfterDiscount() * 0.1 ;
    }
    
    public function getGrandTotal(){
        return $this->getAmountAfterDiscount() + $this->getVatAmount();
    }
    // Sale agent
    public function saler(){
        return $this->belongsTo(\App\Models\User::class,'saler_id','id');
    }
    // Client
    public function client(){
        return $this->belongsTo(\App\Models\User::class,'client_id','id');
    }
    // Payment
    // public function payment(){
    //     return $this->belongsTo(\App\Models\Payment::class,'payment_id','id');
    // }
    // Discount
    public function discount(){
        return $this->belongsTo(\App\Models\Sale\Discount::class,'discount_id','id');
    }
    // Store
    public function store(){
        return $this->belongsTo(\App\Models\Sale\Store::class,'store_id','id');
    }
}
