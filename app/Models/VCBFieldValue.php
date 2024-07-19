<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VCBFieldValue extends Model
{
    protected $table = "v_c_b_field_values" ;
    use softDeletes;
    protected $guarded = [ 'id' ];

    public function field(){
        return $this->hasMany(\App\Models\VCBField::class,'field_id','id');
    }

}

