<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Campaign extends Model
{
    protected $table = 'campaign';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getCampaign($id, $date = '', $isActive = '')
    {
        return Campaign::where('id', $id)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->where('is_active', $isActive)
            ->first();
    }

    public static function getCampaignById($id)
    {
        return Campaign::where('id', $id)->first();
    }

    public static function getCampaignByCondition($options)
    {
        $query = Campaign::select('*');
        if (isset($options['campaign_id']) && !empty($options['campaign_id'])) {
            $query->where('id', $options['campaign_id']);
        }

        if (isset($options['slug']) && !empty($options['slug'])) {
            $query->where('slug', $options['slug']);
        }

        if (isset($options['keyword']) && !empty($options['keyword'])) {
            $query->where('keyword', $options['keyword']);
        }

        if (isset($options['date']) && !empty($options['date'])) {
            $query->whereDate('start_date', '<=', $options['date']);
            $query->whereDate('end_date', '>=', $options['date']);
        }
        if (isset($options['is_active'])) {
            $query->where('is_active', $options['is_active']);
        }
        $query = $query->first();
        return $query;
    }

    public static function getCampaigns($options){
        $query = Campaign::select('*');

        if (isset($options['campaign_id']) && !empty($options['campaign_id'])) {
            if(is_array($options['campaign_id'])){
                $query->whereIn('id', $options['campaign_id']);
            }else{
                $query->where('id', $options['campaign_id']);
            }
        }

        if (isset($options['keyword']) && !empty($options['keyword'])) {
            $query->where('keyword', $options['keyword']);
        }

        $query->orderBy('id', 'DESC');
        return $query->get();
    }

    public static function getCampaignBySlug($role, $date, $slug, $isActive){
        return Campaign::whereIn('role', $role)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->where('is_active', $isActive)
            ->where('slug', $slug)
            ->first();
    }
}
