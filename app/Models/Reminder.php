<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{


    const REMINDER_TYPE_SEND_LINK_SPIN_NOT_USED_ALL   = 1; //campaign_customer: remaining > 0

    protected $table = 'reminder';
    protected $primaryKey = 'reminder_id';

    protected $guarded = [];

}
