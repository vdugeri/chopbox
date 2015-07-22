<?php

namespace ChopBox;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    
   protected $fillable = ['role_id', 'user_id'];


    public function users()
    {
      $return $this->belongsToMany('ChopBox\User');
    }
}
