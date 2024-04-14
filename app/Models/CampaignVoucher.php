<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignVoucher extends Model
{
    protected $table = 'campaign_voucher';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getVoucherByType($campaignId, $type)
    {
        return CampaignVoucher::where('type', $type)
            ->where('campaign_id', $campaignId)
            ->where('state', 0)
            ->lockForUpdate()
            ->first();
    }
}
