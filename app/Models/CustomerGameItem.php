<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerGameItem extends Model
{
    protected $table = 'customer_game_item';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getCustomerGame($filters, $isUpdate = false){
        $query = CustomerGameItem::select('*');

        if(isset($filters['campaign_id']) && !empty($filters['campaign_id'])){
            $query->where('campaign_id', $filters['campaign_id']);
        }

        if(isset($filters['customer_id']) && !empty($filters['customer_id'])){
            $query->where('customer_id', $filters['customer_id']);
        }

        if(isset($filters['item_id']) && !empty($filters['item_id'])){
            $query->whereIn('item_id', $filters['item_id']);
        }

        if($isUpdate){
            $query->lockForUpdate();
        }

        $query->orderBy('item_id');
        $query = $query->get();

        return $query;
    }
}
