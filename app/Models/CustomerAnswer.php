<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class CustomerAnswer extends Model
{
    protected $table = 'customer_answer';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getCustomerAnswer($options, $isUpdate = false, $isFirst = false){
        $query = CustomerAnswer::select('*');

        if(isset($options['customer_id']) && !empty($options['customer_id'])){
            $query->where('customer_id', $options['customer_id']);
        }

        if(isset($options['question_id']) && !empty($options['question_id'])){
            $query->where('question_id', $options['question_id']);
        }

        if (isset($options['correct']) && !empty($options['correct'])) {
            $query->where('correct', $options['correct']);
        }

        if(isset($options['campaign_id']) && !empty($options['campaign_id'])){
            $query->where('campaign_id', $options['campaign_id']);
        }

        if(isset($options['type']) && !empty($options['type'])){
            $query->where('type', $options['type']);
        }

        if($isUpdate){
            $query->lockForUpdate();
        }

        $query->orderBy('question_id');

        if(isset($options['group_by']) && !empty($options['group_by'])){
            $query->groupBy($options['group_by']);
        }

        if ($isFirst) {
            $query = $query->first();
        } else {
            $query = $query->get();
        }

        return $query;
    }

    public static function getAnswer($options){
        $query = DB::table('customer_answer as ca')->select('ca.*', 'qa.answer')
            ->join('question_answer as qa', 'qa.id', '=', 'ca.answer_id');

        if(isset($options['customer_id']) && !empty($options['customer_id'])){
            $query->where('ca.customer_id', $options['customer_id']);
        }

        if(isset($options['campaign_id']) && !empty($options['campaign_id'])){
            $query->where('ca.campaign_id', $options['campaign_id']);
            $query->where('qa.campaign_id', $options['campaign_id']);
        }

        $query->groupBy('ca.question_id');
        $query->orderBy('ca.question_id');

        $query = $query->get();

        return $query;
    }

    public static function insertCustomerAnswer($questionId, $campaignId, $customerId, $type, $time){
        return  CustomerAnswer::create([
            'question_id'   => $questionId,
            'customer_id'   => $customerId,
            'campaign_id'   => $campaignId,
            'type'          => $type,
            'start_time'    => date('Y-m-d H:i:s', strtotime(now())),
            'end_time'      => date('Y-m-d H:i:s', (strtotime(now()) + $time))
        ]);
    }

    public static function getAll($options = []){
        $subQuery = CustomerAnswer::select('id as sid')
        ->where('campaign_id', $options['campaign_id'])
        ->groupBy('question_id')
        ->groupBy('customer_id');

        $query = CustomerAnswer::select(
        'customer.id as cus_id','customer.name as customer_name','customer.code','customer.phone','customer.region',
            DB::raw('SUM(correct) AS total_correct'),
            DB::raw('SUM(total_time) AS total_time_game'),
            DB::raw('MIN(start_time) AS start_time_game'),
            DB::raw('MAX(end_time) AS end_time_game')
        )
        ->join(DB::raw('(' . $subQuery->toSql() . ') s'), 'customer_answer.id', '=', 's.sid')
        ->mergeBindings($subQuery->getQuery())
        ->join('customer','customer.id','=','customer_answer.customer_id')
        ->where('customer_answer.campaign_id', $options['campaign_id']);

        if(isset($options['phone']) && !empty($options['phone'])){
            $query->where('customer.phone', $options['phone']);
        }
        if(isset($options['region']) && !empty($options['region'])){
            $query->where('customer.region', $options['region']);
        }
        if(isset($options['code']) && !empty($options['code'])){
            $query->where('customer.code', $options['code']);
        }
        if(isset($options['start_date']) && !empty($options['start_date'])){
            $query->whereDate('customer_answer.created_at', '>=', $options['start_date']);
        }
        if(isset($options['end_date']) && !empty($options['end_date'])){
            $query->whereDate('customer_answer.created_at', '<=', $options['end_date']);
        }

        $query->groupBy('customer_answer.customer_id');
        $query->orderBy('total_correct', 'DESC');
        $query->orderBy('total_time_game');

        if(isset($options['items']) && $options['items'] > 0){
            return $query->paginate($options['items']);
        }
        return $query->get();
    }
}
