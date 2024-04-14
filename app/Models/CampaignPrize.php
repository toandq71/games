<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class CampaignPrize extends Model
{
    protected $table = 'campaign_prize';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getPrizeByCampaign($campaignId)
    {
        return CampaignPrize::where('campaign_id', $campaignId)->orderBy('position', 'ASC')->get();
    }

    public static function getPrize($campaignId, $type, $isUpdate)
    {
        $query = CampaignPrize::where('campaign_id', $campaignId)->where('type', $type);
        if ($isUpdate) {
            $query->lockForUpdate();
        }
        return $query->first();
    }

    public static function getPrizeForSpin($id, $campaignId, $type){
        return CampaignPrize::where('id', $id)
            ->where('campaign_id', $campaignId)
            ->where('type', $type)
            ->where('remaining', '>', 0)
            ->lockForUpdate()
            ->first();
    }

    public static function getPrizeForSharp($campaignId, $typeCode, $sort, $isUpdate = false)
    {
        $query = CampaignPrize::where('campaign_id', $campaignId)->where('type_code', $typeCode);
        if($isUpdate){
            $query->lockForUpdate();
        }
        $query = $query->orderBy($sort, 'ASC');
        return $query->get();
    }

    public static function getPrizeSharpForSpin($id, $campaignId, $type, $typeCode){
        return CampaignPrize::where('id', $id)
            ->where('campaign_id', $campaignId)
            ->where('type', $type)
            ->where('type_code', $typeCode)
            ->where('remaining', '>', 0)
            ->lockForUpdate()
            ->first();
    }

    public static function getPrizeRandomForSharp($campaignId, $typeCode)
    {
        $query = "CAST(rate AS DECIMAL(10,2)) DESC";
        return CampaignPrize::where('campaign_id', $campaignId)
            ->where('type_code', $typeCode)
            ->lockForUpdate()
            ->orderByRaw($query)
            ->get();
    }

    public static function getPrizeCondition($filters)
    {
        $query = DB::table('campaign_prize AS cp')->select('cp.*', 'ca.name AS campaign_name')->join('campaign AS ca', 'ca.id', '=', 'cp.campaign_id');
        if (isset($filters['campaign_id']) && !empty($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        if (isset($filters['type_code']) && !empty($filters['type_code'])) {
            $query->where('type_code', $filters['type_code'])->orderBy('type_code', 'ASC');
        }
        return $query->get();
    }

    public static function getPrizeRandomForCircle($campaignId, $types)
    {
        $query = "CAST(rate AS DECIMAL(10,2)) DESC";
        return CampaignPrize::where('campaign_id', $campaignId)
            ->whereNotIn('type', $types)
            ->lockForUpdate()
            ->orderByRaw($query)
            ->get();
    }

    public static function getPrizeRandomForGuardian($campaignId, $quantity = 0)
    {
        $subQuery = "CAST(rate AS DECIMAL(10,2)) DESC";
        $query = CampaignPrize::where('campaign_id', $campaignId)->where('remaining', '>', 0);

        if($quantity > 0){
            $query->where('quantity', '>', 0);
        }
        $query->lockForUpdate();
        $query->orderByRaw($subQuery);
        $query = $query->get();
        return $query;
    }

    public static function getPrizeSunlight($filters, $isFirst = false, $isUpdate = false)
    {
        $query = CampaignPrize::select('*');
        if (isset($filters['campaign_id']) && !empty($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        if (isset($filters['id']) && !empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        if (isset($filters['region']) && !empty($filters['region'])) {
            $query->where('region', $filters['region']);
        }

        if (isset($filters['type']) && !empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if($isUpdate){
            $query->lockForUpdate();
        }

        if($isFirst){
            $query = $query->first();
        }else{
            $query = $query->orderBy('position', 'ASC')->get();
        }
        return $query;
    }

    public static function getPrizeForRandom($campaignId){
        return CampaignPrize::where('campaign_id', $campaignId)
            ->lockForUpdate()
            ->get();
    }

    public static function getPrizeVCMin($campaignId, $types){
        return CampaignPrize::where('campaign_id', $campaignId)
            ->where('remaining', '>', 0)
            ->whereIn('type', $types)
            ->orderBy('type', 'ASC')
            ->first();
    }

    public static function getPrizeGuardian($campaignId, $types)
    {
        $query = "CAST(rate AS DECIMAL(10,2)) DESC";
        return CampaignPrize::where('campaign_id', $campaignId)
            ->whereNotIn('type', $types)
            ->lockForUpdate()
            ->orderByRaw($query)
            ->get();
    }

    public static function getPrizeSpinU($campaignId, $type, $typeCode, $isUpdate = false){
        $query = CampaignPrize::where('campaign_id', $campaignId)->where('type_code', $typeCode)->whereNotIn('type', $type);
        $subQuery = "CAST(rate AS DECIMAL(10,2)) DESC";

        if($isUpdate){
            $query->lockForUpdate();
            $query->orderByRaw($subQuery);
        }else{
            if($typeCode == 1){
                $query->where('quantity', '>', 0);
                $query->orderByRaw($subQuery);
            }else{
                $query->where('quantity', 0);
                $query->orderByRaw($subQuery);
            }
        }
        $query = $query->get();
        return $query;
    }

    public static function getPrizeRandom($campaignId){
        return CampaignPrize::where('campaign_id', $campaignId)
            ->lockForUpdate()
            ->get();
    }

    public static function getCampaignPrizeForSpin($campaignId)
    {
        return CampaignPrize::select('id')->where('campaign_id', $campaignId)->orderBy('position', 'ASC')->get();
    }

    public static function getPrizeRealRandom($campaignId, $isUpdate = false){
        $query = CampaignPrize::where('campaign_id', $campaignId)
            ->where('quantity', '>', 0);
        if($isUpdate){
            $query->lockForUpdate();
        }
        $query = $query->get();
        return $query;
    }

    public static function getListByTypes($campaignId, $types){
        return CampaignPrize::where('campaign_id', $campaignId)->whereIn('type', $types)->get();
    }

    public static function updateCampaignPrizeById($id, $arrayData = [])
    {
        $result = CampaignPrize::where('id', $id)
            ->update($arrayData);
        return $result;
    }
}
