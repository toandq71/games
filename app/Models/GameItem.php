<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameItem extends Model
{
    protected $table = 'game_item';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getGameItem($options)
    {
        $query = GameItem::select('*');

        if (isset($options['campaign_id']) && !empty($options['campaign_id'])) {
            $query->where('campaign_id', $options['campaign_id']);
        }
        if (isset($options['type']) && !empty($options['type'])) {
            $query->where('type', $options['type']);
        }
        if (isset($options['ids']) && !empty($options['ids'])) {
            $query->whereIn('id', $options['ids']);
        }
        $query = $query->get();
        return $query;
    }

    public static function getGameItemNotPlay($ids, $campaignId){
        $query = GameItem::where('campaign_id', $campaignId)
            ->whereNotIn('id', $ids)
            ->get();
        return $query;
    }

    public static function getById($id, $campaignId){
        return GameItem::where('id', $id)
            ->where('campaign_id', $campaignId)
            ->first();
    }

    public static function getItems($options)
    {
        $query = GameItem::select('id', 'image', 'position');

        if (isset($options['campaign_id']) && !empty($options['campaign_id'])) {
            $query->where('campaign_id', $options['campaign_id']);
        }
        if (isset($options['type']) && !empty($options['type'])) {
            $query->where('type', $options['type']);
        }
        if (isset($options['ids']) && !empty($options['ids'])) {
            $query->whereIn('id', $options['ids']);
        }
        $query = $query->get();
        return $query;
    }
}
