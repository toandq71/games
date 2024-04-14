<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Response extends Model
{
    protected $table = 'response';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getResponses($filters){
        $query = DB::table('response AS re')->select(
            're.phone',
            're.message',
            're.campaign_id',
            're.status',
            're.type',
            're.created_at',
            're.updated_at',
            're.provider',
            'cu.name',
            'cu.code',
            'cp.name AS campaign_name',
            're.total'
        )
            ->join('customer AS cu', 'cu.phone', '=', 're.phone')
            ->join('campaign AS cp', 'cp.id', '=', 're.campaign_id')
            ->where('re.type', $filters['type'])
            ->whereIn('re.campaign_id', $filters['ids'])
            ->whereIn('cu.campaign_id', $filters['ids']);

        if(isset($filters['phone']) && !empty($filters['phone'])){
            $query->where('re.phone', $filters['phone']);
        }

        if(isset($filters['start_date']) && !empty($filters['start_date'])){
            $query->whereDate('re.created_at', '>=', $filters['start_date']);
        }
        if(isset($filters['end_date']) && !empty($filters['end_date'])){
            $query->whereDate('re.created_at', '<=', $filters['end_date']);
        }

        if($filters['status'] != ''){
            $query->where('re.status', $filters['status']);
        }
        $query->orderBy('re.created_at', 'DESC');

        if($filters['pagination']){
            $query = $query->paginate($filters['items']);
        }

        return $query;
    }

    public static function getListToSendSMS($filters){
        $query = Response::where('type', $filters['type'])
            ->whereIn('campaign_id', $filters['ids']);

        if(isset($filters['phone']) && !empty($filters['phone'])){
            $query->where('phone', $filters['phone']);
        }

        if(isset($filters['start_date']) && !empty($filters['start_date'])){
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if(isset($filters['end_date']) && !empty($filters['end_date'])){
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if($filters['status'] != ''){
            $query->where('status', $filters['status']);
        }

        $query->orderBy('created_at', 'DESC');
        $query = $query->lockForUpdate()->get();

        return $query;
    }
}
