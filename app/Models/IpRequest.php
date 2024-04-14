<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class IpRequest extends Model
{
    protected $table = 'ip_request';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getIpRequest($filters){
        return IpRequest::where('campaign_id', $filters['campaign_id'])
            ->where('phone', $filters['phone'])
            ->where('ip', $filters['ip'])
            ->first();
    }
    public static function getByIp($ip, $campaignId){
        return IpRequest::where('campaign_id', $campaignId)
            ->where('ip', $ip)
            ->first();
    }
}
