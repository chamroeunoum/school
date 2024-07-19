<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'] ;
    /**
     * Relationships
     */
    public function variants(){
        return $this->hasMany(\App\Models\Product\Variant::class,'attribute_id','id');
    }
}
