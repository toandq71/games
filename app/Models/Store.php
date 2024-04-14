<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $table = 'store';
    protected $primaryKey = 'id';

    protected $guarded = [];


    public static function getStore($filters, $isFirst = false)
    {
        $query = Store::select('*');

        if(isset($filters['code']) && !empty($filters['code'])){
            $query->where('code', $filters['code']);
        }
        if(isset($filters['campaign_id']) && !empty($filters['campaign_id'])){
            $query->where('campaign_id', $filters['campaign_id']);
        }
        if(isset($filters['store_id']) && !empty($filters['store_id'])){
            $query->where('id', $filters['store_id']);
        }

        if($isFirst){
            $query = $query->first();
        }else{
            $query = $query->get();
        }
        return $query;
    }
}
