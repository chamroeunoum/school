<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variant extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
     /**
     * Relationships
     */
    public function attribute(){
        return $this->belongsTo(\App\Models\Product\Attribute::class,'attribute_id','id');
    }
}
