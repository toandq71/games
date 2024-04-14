<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class Admin extends Model implements AuthenticatableContract,
                                    CanResetPasswordContract
{
  use Authenticatable, CanResetPassword ;

  const AUTH_GRUARD     = 'admin';

  protected $table      = 'admin';

  protected $primaryKey = 'id';

  protected $guarded    = [];

  protected $hidden     = ['password', 'remember_token'];


    public function roles() {
        return $this->belongsToMany('App\Models\Role', 'role_user', 'user_id', 'role_id');
    }

    public function getRoles() {
        $roles = [];
        if ($this->roles()) {
            $roles = $this->roles()->get();
        }
        return $roles;
    }

    /***
     * @param $role
     * @return mixed
     */
    public function hasRole($currentRole)
    {
        $results = $this->getRoles();

        if($results){
            $roles = [];
            foreach ($results as $result){
                array_push($roles, $result['name']);
            }
            if($roles){
                $flag = false;
                foreach ($currentRole as $item){
                    if(in_array($item, $roles)){
                        $flag = true;
                    }
                }
                return $flag;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}
