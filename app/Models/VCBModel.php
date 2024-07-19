<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VCBModel extends Model
{
    protected $table = "v_c_b_models" ;
    use softDeletes;
    protected $guarded = [ 'id' ];


    /**
     * Relationships
     */
    public function tags(){
        return $this->hasMany(\App\Models\Tag::class,'model_id','id');
    }
    public function fields(){
        return $this->hasMany(\App\Models\Field::class,'model_id','id');
    }

    /**
     * Functions
     */
    // Get the class of the current model
    public function class(){
        return $this->class !== "" ? $this->class::class : null ;
    }
}
