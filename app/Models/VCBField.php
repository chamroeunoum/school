<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VCBField extends Model
{
    protected $table = "v_c_b_fields" ;
    use HasFactory, softDeletes;
    protected $guarded = [ 'id' ];

    /**
     * Relationships
     */
    public function model(){
        return $this->belongsTo(\App\Models\VCBModel::class,'model_id','id');
    }
    public function values(){
        return $this->hasMany(\App\Models\VCBFieldValue::class,'field_id','id');
    }
    
}
