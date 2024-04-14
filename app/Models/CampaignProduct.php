<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignProduct extends Model
{
    protected $table = 'campaign_product';
    protected $primaryKey = 'id';

    protected $guarded = [];


    public static function compareProduct($ids){
        return CampaignProduct::whereIn('product_id', $ids)->get();
    }
}
