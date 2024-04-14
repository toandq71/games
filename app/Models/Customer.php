<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getCustomerByPhone($phone){
        return Customer::where('phone', $phone)->lockForUpdate()->first();
    }

    public static function getCustomerById($id){
        return Customer::where('id', $id)->first();
    }

    public static function getCustomer($options, $isUpdate = false){
        $query = Customer::select('*');

        if(isset($options['code']) && !empty($options['code'])){
            $query->where('code', $options['code']);
        }

        if(isset($options['phone']) && !empty($options['phone'])){
            $query->where('phone', $options['phone']);
        }

        if(isset($options['campaign_id']) && !empty($options['campaign_id'])){
            $query->where('campaign_id', $options['campaign_id']);
        }
        if(isset($options['id']) && !empty($options['id'])){
            $query->where('id', $options['id']);
        }
        if(isset($options['isJoin']) && !empty($options['isJoin'])){
            $query->join('user_cookie', 'user_cookie.phone',  'customer.phone');
        }
        if(isset($options['isJoinCamp']) && !empty($options['isJoinCamp'])){
            $query->join('campaign_customer', 'campaign_customer.id',  'customer.id');
        }
        if(isset($options['phonenumber']) && !empty($options['phonenumber'])){
            $query->where('customer.phone', $options['phonenumber']);
        }

        if($isUpdate){
            $query->lockForUpdate();
        }

        return $query->first();
    }
    public static function getListCustomer($options){
        $query = Customer::select('*');

        if(isset($options['code']) && !empty($options['code'])){
            $query->where('code', $options['code']);
        }

        if(isset($options['phone']) && !empty($options['phone'])){
            $query->where('phone', 'like','%'.$options['phone'].'%');
        }

        if(isset($options['campaign_id']) && !empty($options['campaign_id'])){
            $query->where('campaign_id', $options['campaign_id']);
        }
        if(isset($options['id']) && !empty($options['id'])){
            $query->where('id', $options['id']);
        }
        if(isset($options['start_date']) && !empty($options['start_date'])){
            $query->whereDate('created_at', '>=', $options['start_date']);
        }

        if(isset($options['end_date']) && !empty($options['end_date'])){
            $query->whereDate('created_at', '<=', $options['end_date']);
        }

        if(isset($options['orderById']) && !empty($options['orderById'])){
            $query->orderBy('id', $options['orderById']);
        }

        if(isset($options['pagination']) && $options['pagination'] == true){
            return $query->paginate($options['items']);
        }
        return $query->get();
    }

}
