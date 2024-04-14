<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class CoverPage extends Model
{
    protected $table = 'cover_page';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getAll(){
        return CoverPage::whereNull('uuid')->get();
    }
}
