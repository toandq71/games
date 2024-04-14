<?php

# @Author: XuanDo
# @Date:   2018-02-08T20:49:12+07:00
# @Email:  ngocxuan2255@gmail.com
# @Last modified by:   Xuan Do
# @Last modified time: 2018-03-05T18:25:38+07:00
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductEleven extends Model
{

    public $primaryKey = 'id';

    public $table = 'product_eleven';

    public $guarded = [];

    protected $hidden = [];

    public static function getProductEleven(){
        return ProductEleven::select('*')->pluck('product_id')->toArray();
    }
}
