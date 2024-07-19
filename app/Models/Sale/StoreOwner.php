<?php

namespace App\Models\Sale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreOwner extends Model
{
    use HasFactory, SoftDeletes ;
    protected $guarded = ['id'];
}