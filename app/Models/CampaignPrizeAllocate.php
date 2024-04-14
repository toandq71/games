<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class CampaignPrizeAllocate extends Model
{
    public $primaryKey = 'id';

    public $table = 'campaign_prize_allocate';

    public $guarded = [];

    protected $hidden = [];

    public static function getPrizeAllocate($campaignId, $date, $types, $region)
    {
        $query = CampaignPrizeAllocate::where('campaign_id', $campaignId)
            ->whereDate('date', '<=', $date)
            ->where('region', $region)
            ->whereIn('type', $types)
            ->orderBy('date')
            ->get();
        return $query;
    }
    public static function getAllocateById($id){
        $query = CampaignPrizeAllocate::where('id', $id)
            ->where('remaining', '>', 0)
            ->lockForUpdate()
            ->first();
        return $query;
    }

    public static function updateAllocate($campaignId, $type, $data, $options){
        $query = CampaignPrizeAllocate::where('campaign_id', $campaignId)
            ->where('type', $type);
        if(isset($options['region']) && !empty($options['region'])){
            $query->where('region', $options['region']);
        }
        if(isset($options['id']) && !empty($options['id'])){
            $query->where('id', $options['id']);
        }
        $query = $query->update($data);
        return $query;
    }

    public static function getPrizeAllocateByCondition($options){
        $query = CampaignPrizeAllocate::where('campaign_id', $options['campaign_id']);

        if(isset($options['region']) && !empty($options['region'])){
            $query->whereDate('region', $options['region']);
        }

        if(isset($options['type']) && !empty($options['type'])){
            $query->whereIn('type', $options['type']);
        }
        if(isset($options['is_process'])){
            $query->where('is_process', $options['is_process']);
        }
        if(isset($options['phone']) && !empty($options['phone'])){
            $query->where('phone', $options['phone']);
        }

        if(isset($options['remaining'])){
            $query->where('remaining', '>' , $options['remaining']);
        }

        if(isset($options['date']) && !empty($options['date'])){
            $query->whereDate('date', '<=', $options['date']);
            $query->orderBy('date');
        }
        $query = $query->get();
        return $query;
    }

    public static function getTotalLimit($campaignId, $date, $types, $region)
    {
        return  CampaignPrizeAllocate::where('campaign_id', $campaignId)
            ->whereDate('date', '<=', $date)
            ->where('region', $region)
            ->whereIn('type', $types)
            ->orderBy('date')
            ->sum('total');
    }

    public static function getPrizeAllocateForRandom($campaignId, $types, $region, $date)
    {
        $query = CampaignPrizeAllocate::where('campaign_id', $campaignId)
            ->whereDate('date', '<=', $date)
            ->where('region', $region)
            ->whereIn('type', $types)
            ->where('remaining', '>', 0)
            ->first();
        return $query;
    }

    public static function updateRemainingAllocate($id){
        return CampaignPrizeAllocate::where('id', $id)->lockForUpdate()->update(['remaining' => DB::raw('remaining+1'), 'used' => DB::raw('used-1')]);
    }
}
