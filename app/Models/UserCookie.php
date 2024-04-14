<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCookie extends Model
{
    public $primaryKey = 'id';

    public $table = 'user_cookie';

    public $guarded = [];

    protected $hidden = [];
}
