<?php

namespace ChopBox;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    //

  private $user_id;
  private $role_id;



  public function __construct($user_id, $role_id)
  {
    $this->role_id = $role_id;
    $tjis->user_id = $user_id;
  }


  /*
   * Persist object to database
  */

  public function persist()
  {
    $tis->save();
  }
}
