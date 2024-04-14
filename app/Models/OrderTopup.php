<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTopup extends Model
{
    protected $table = 'order_topup';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public static function getOrderTopupByCondition($options, $isUpdate = false){
        $query = OrderTopup::select('order_topup.*','customer.phone','customer.code','campaign.name as campaign_name')
            ->join('customer','customer.id','=','order_topup.customer_id')
            ->join('campaign','campaign.id','=','order_topup.campaign_id');

        if(isset($options['campaign_id']) && !empty($options['campaign_id'])){
            $query->where('order_topup.campaign_id', $options['campaign_id']);
        }else{
            $query->where('order_topup.campaign_id', 'fakeid_campaign');
        }

        if(isset($options['code']) && !empty($options['code'])){
            $query->where('customer.code', $options['code']);
        }

        if(isset($options['phone']) && !empty($options['phone'])){
            $query->where('customer.phone', $options['phone']);
        }

        if(isset($options['customer_id']) && !empty($options['customer_id'])){
            $query->where('order_topup.customer_id', $options['customer_id']);
        }

        if(isset($options['start_date']) && !empty($options['start_date'])){
            $query->whereDate('order_topup.created_at', '>=', $options['start_date']);
        }

        if(isset($options['end_date']) && !empty($options['end_date'])){
            $query->whereDate('order_topup.created_at', '<=', $options['end_date']);
        }

        if(isset($options['state']) && $options['state'] !== ''){
            $query->where('order_topup.state', $options['state']);
        }

        if($isUpdate){
            $query = $query->lockForUpdate()->first();
        }else{
            if(isset($options['items']) && $options['items'] > 0){
                $query = $query->paginate($options['items']);
            }else{
                if(isset($options['action']) && $options['action'] == 'export'){
                    return $query;
                }
                $query = $query->get();
            }
        }
        return $query;
    }

    public static function getRowTopup($id, $campaignId, $customerId){
        return OrderTopup::where('id', $id)
            ->where('campaign_id', $campaignId)
            ->where('customer_id', $customerId)
            ->first();
    }
    public static function checkSendGiftToCustomer($campaignId, $customerId){
        return OrderTopup::where('campaign_id', $campaignId)
            ->where('customer_id', $customerId)
            ->where('is_delete', 0)
            ->where('state', 0)
            ->count();
    }
}
