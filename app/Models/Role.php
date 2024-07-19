<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = ['name', 'updated_at', 'created_at','guard_name','tag'];
}