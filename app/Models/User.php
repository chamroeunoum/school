<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\Base\app\Notifications\ResetPasswordNotification as ResetPasswordNotification;
// use Backpack\CRUD\CrudTrait;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\File;
use Storage;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname', 'lastname', 'email', 'password','phone', 'active', 'activation_token', 'avatar' , 'avatar_url' , 'username' 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password', 'remember_token', 'activation_token'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    protected $is_admin = true;

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function getName(){
      $user = $this->find($this->id);
      return $user->lastname.' '.$user->firstname;
    }
    public function getStatusLayout(){
      $layout = 'Status not defined' ;
      $user = $this->find($this->id) ;
      switch($user->status){
        case 1:
        // user is allowed to login to system, account is active
        $layout = '<span class="label label-success" >Active</span>' ;
          break;
        case 2:
        // user is verifing, waiting for the email to confirm
          break;
        case 4:
        // user is activated, email has confirmed
          break;
        case 8:
        // user is disabled, account is disabled for some reason
          break;
        default:
          //  user is blocked from login to system, account is not active
          $layout = '<span class="label label-danger" >Blocked</span>' ;
          break;
        }
      return $layout ;
    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function roles(){
      return $this->belongsToMany('App\Models\Role','user_role','user_id','role_id');
    }

    public function countesies()
    {
        return $this->hasMany('App\Models\Countesy','countesy_id','id');
    }

    public function countesy()
    {
        return $this->belongsTo('App\Models\Countesy','countesy_id','id');
    }

    public function dlists()
    {
        return $this->hasMany('App\Models\Dlist','user_id','id');
    }

    public function groups()
    {
        return $this->hasMany('App\Models\Group','group_id','id');
    }

    public function ministries()
    {
        return $this->hasMany('App\Models\Ministry','ministry_id','id');
    }
    /**
     * ឯកសារដែលគណនីមួយនេះបានបង្កើត
     */
    public function documents(){
        return $this->hasMany(\App\Models\Document::class,'created_by');
    }

    public function person(){
      return $this->belongsTo('App\Models\People','people_id','id');
    }

    public function ownStores(){
      return $this->belongsToMany(\App\Models\Sale\Store::class,'store_owners','user_id','store_id');
    }

    public function stores(){
      return $this->belongsToMany(\App\Models\Sale\Store::class,'store_users','user_id','store_id');
    }

    public function setImageAttribute($value)
    {
        $attribute_name = "image";
        $disk = "uploads";
        $destination_path = "profiles";

        // if the image was erased
        if ($value==null) {
            // delete the image from disk
            \Storage::disk($disk)->delete($this->image);

            // set null in the database column
            $this->attributes[$attribute_name] = null;
        }

        // if a base64 was sent, store it in the db
        if (starts_with($value, 'data:image'))
        {
            // 0. Make the image
            $image = \Image::make($value);
            // 1. Generate a filename.
            $filename = md5($value.time()).'.jpg';
            // 2. Store the image on disk.

            // but delete the existing image first
            if( !$image->filesize()){
              $image_path = public_path().'/'.$this->image;
              if(\File::isFile($image_path))unlink($image_path);
              // then store the new image to the profiles folder
              \Storage::disk($disk)->put($destination_path.'/'.$filename, $image->stream());
              // 3. Save the path to the database
              $this->attributes[$attribute_name] = $disk.'/'. $destination_path.'/'.$filename;
              if($this->people_id>0){
                $people = \App\Models\People::find($this->people_id);
                $people->image = $disk.'/'. $destination_path.'/'.$filename;
                $people->save();
              }
            }

        }
    }

    public function folders(){
      return $this->hasMany('\App\Models\Folder','user_id','id');
    }

    public function getIsAdminAttribute()
    {
        return true;
    }

    public static function boot()
    {
        parent::boot();
        static::deleting(function($obj) {
            \Storage::disk('uploads')->delete($obj->image);
        });
    }
    /**
     * Scope a query to only include active users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeClient($query)
    {
        return $query->whereHas( 'roles' , function( $query ){
          $query->where('name','Client');
        }); 
    }
}
