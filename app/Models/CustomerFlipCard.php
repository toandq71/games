<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerFlipCard extends Model
{
    protected $table = 'customer_flipcards';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getListByCustomer($campaignId, $customerId, $orderId){
        return CustomerFlipCard::where('campaign_id', $campaignId)
            ->where('customer_id', $customerId)
            ->where('order_id', $orderId)
            ->groupBy('position')
            ->get();
    }

    public static function checkExistsOrderId($campaignId, $orderId){
        return CustomerFlipCard::where('campaign_id', $campaignId)
            ->where('order_id', $orderId)
            ->first();
    }
}
