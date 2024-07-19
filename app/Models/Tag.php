<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    protected $table = "tags" ;
    use softDeletes;
    protected $guarded = [ 'id' ];

    /**
     * Relationships
     */
    public function model(){
        return $this->hasMany(\App\Models\VCBModel::class,'model_id','id');
    }
    public function products(){
        return $this->hasMany(\App\Models\Product\Product::class,'tag_id','id');
    }
    public function children(){
        return $this->hasMany(\App\Models\Tag::class,'pid','id');
    }
    public function dady(){
        return $this->belongsTo(\App\Models\Tag::class,'pid','id');
    }

}
