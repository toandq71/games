<?php

# @Author: XuanDo
# @Date:   2018-02-08T20:49:12+07:00
# @Email:  ngocxuan2255@gmail.com
# @Last modified by:   Xuan Do
# @Last modified time: 2018-03-05T18:25:38+07:00
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class CustomerEleven extends Model
{

    public $primaryKey = 'id';

    public $table = 'customer_eleven';

    public $guarded = [];

    protected $hidden = [];


    public static function getCustomerEleven($options, $limit = 0){
        $query = CustomerEleven::select('*');

        if(isset($options['phone']) && !empty($options['phone'])){
            $query->where('phone', $options['phone']);
        }
        if(isset($options['bill_code']) && !empty($options['bill_code'])){
            $query->where('bill_code', $options['bill_code']);
        }
        if(isset($options['campaign_id']) && !empty($options['campaign_id'])){
            $query->where('campaign_id', $options['campaign_id']);
        }
        if(isset($options['uuid']) && !empty($options['uuid'])){
            $query->where('uuid', $options['uuid']);
        }
        if(isset($options['bill_code']) && !empty($options['bill_code'])){
            $query->where('bill_code', $options['bill_code']);
        }
        if($limit > 0){
            /// lay tat ca
            return $query->get();
        }else{
            return $query->lockForUpdate()->first();
        }
    }
}
