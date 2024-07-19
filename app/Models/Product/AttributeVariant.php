<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeVariant extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    /**
     * Functions
     */
    public function variantCombination(){
        return \App\Models\Product\Variant::whereIn('id',$this->variants);
    }

    public function setVariantsAttribute($val)
    {
        $this->attributes['variants'] = is_array( $val ) && !empty( $val ) ? implode(',',$val) : $val ;
    }
    public function getVariantsAttribute($val)
    {
        return $this->attributes['variants'] = is_array( $val ) && !empty( $val ) ? $val : explode(',',$val);
    }
}
