<?php

namespace App\Models;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Model;
use DB;

class Order extends Model
{
    protected $table = 'order';
    protected $primaryKey = 'id';

    protected $guarded = [];


    public static function getOrder($campaignId, $uuid, $isUpdate = false)
    {
        $query = Order::where('campaign_id', $campaignId)->where('uuid', $uuid);
        if ($isUpdate) {
            $query->where('state', 0);
            $query->lockForUpdate();
        }
        return $query->first();
    }


    public static function getOrderByPhone($phone, $campaignId, $uuid = '')
    {
        $query = Order::where('phone', $phone)
            ->where('campaign_id', $campaignId)
            ->where('state', 1);

        if (!empty($uuid)) {
            $query = $query->where('uuid', $uuid);
        }

        $query = $query->lockForUpdate();
        $query = $query->get();
        return $query;
    }

    public static function getOrderByCondition($options, $isUpdate = false){
        $query = Order::select('*');
        if(isset($options['campaign_id']) && !empty($options['campaign_id'])){
            $query->where('campaign_id', $options['campaign_id']);
        }
        if(isset($options['uuid']) && !empty($options['uuid'])){
            $query->where('uuid', $options['uuid']);
        }

        if(isset($options['phone']) && !empty($options['phone'])){
            $query->where('phone', $options['phone']);
        }

        if(isset($options['order_id']) && !empty($options['order_id'])){
            $query->where('id', $options['order_id']);
        }

        if(isset($options['customer_id']) && !empty($options['customer_id'])){
            $query->where('customer_id', $options['customer_id']);
        }

        if(isset($options['state'])){
            $query->where('state', $options['state']);
        }

        if(isset($options['pos'])){
            $query->where('pos', $options['pos']);
        }

        if ($isUpdate) {
            $query = $query->lockForUpdate()->first();
        } elseif (isset($options['getFirst'])) {
            $query = $query->first();
        }else{
            $query = $query->get();
        }
        return $query;
    }

    public static function checkQuotaPrize($campaignId, $phone, $maxQuota){
        $results = Order::where('phone', $phone)
            ->where('campaign_id', $campaignId)
            ->where('prize_type', '>', 0)
            ->lockForUpdate()
            ->get();

        if(count($results) >= $maxQuota){
            return true;
        }else{
            return false;
        }
    }

    public static function getReportOrder($options, $pagination = false, $joinCustomer = true){
        $query = DB::table('order AS or')->select('or.*');
        if($joinCustomer){
            $query = $query->selectRaw('cu.name as fullname, cu.age')->join('customer AS cu', 'cu.id', '=', 'or.customer_id');
        }
        if(isset($options['campaign_id']) && !empty($options['campaign_id'])){
            $query->where('or.campaign_id', $options['campaign_id']);
        }
        if(isset($options['phone']) && !empty($options['phone'])){
            $query->where('or.phone', $options['phone']);
        }
        if(isset($options['uuid']) && !empty($options['uuid'])){
            $query->where('or.uuid', $options['uuid']);
        }
        if(isset($options['start_date']) && !empty($options['start_date'])){
            $query->whereDate('or.created_at', '>=', $options['start_date']);
        }
        if(isset($options['end_date']) && !empty($options['end_date'])){
            $query->whereDate('or.created_at', '<=', $options['end_date']);
        }
        $query->orderBy('or.created_at', 'DESC');

        if(!$pagination){
            return $query->get();
        }else{
            return $query->paginate($options['items']);
        }
    }

    public static function getOrderByCus($options){
        $query = DB::table('order AS or')->select('or.*', 'cp.image')
            ->join('campaign_prize AS cp', 'cp.type', '=', 'or.prize_type')
            ->where('or.campaign_id', $options['campaign_id'])
            ->where('cp.campaign_id', $options['campaign_id']);

        if(isset($options['customer_id']) && !empty($options['customer_id'])){
            $query->where('customer_id', $options['customer_id']);
        }
        $query = $query->get();
        return $query;
    }

    public static function getOrderBill($filters, $pagination){
        $query = DB::table('order AS or')->select('or.*', 'cb.store_name')
            ->join('customer_bill AS cb', 'cb.uuid', '=', 'or.uuid')
            ->where('cb.campaign_id', $filters['campaign_id'])
            ->where('or.campaign_id', $filters['campaign_id']);

        $query->orderBy('or.created_at', 'DESC');
        if($pagination){
            $query =  $query->paginate($filters['items']);
        }else{
            $query = $query->get();
        }
        return $query;
    }

    public static function checkOrderPhysicalByStore($campaignId, $types, $storeCode){
        $results = Order::where('campaign_id', $campaignId)
            ->where('store_code', $storeCode)
            ->whereIn('prize_type', $types)
            ->lockForUpdate()
            ->get();

        if(count($results) > 0){
            return true;
        }else{
            return false;
        }
    }

    public static function checkQuotaPrizeU($campaignId, $customerIds, $type){
        $result = Order::where('campaign_id', $campaignId)
            ->whereIn('customer_id', $customerIds)
            ->where('prize_type', $type)
            ->lockForUpdate()
            ->first();

        if(!empty($result)){
            return true;
        }else{
            return false;
        }
    }
    public static function getOrders($options, $isUpdate = false){
        $query = Order::select('*');
        if(isset($options['campaign_id']) && !empty($options['campaign_id'])){
            $query->where('campaign_id', $options['campaign_id']);
        }
        if(isset($options['state'])){
            $query->where('state', $options['state']);
        }
        if(isset($options['is_send'])){
            $query->where('is_send', $options['is_send']);
        }
        if(isset($options['uuid']) && !empty($options['uuid'])){
            $query->where('uuid', $options['uuid']);
        }

        if(isset($options['order_id']) && !empty($options['order_id'])){
            $query->where('id', $options['order_id']);
        }

        if(isset($options['customer_id']) && !empty($options['customer_id'])){
            $query->where('customer_id', $options['customer_id']);
        }

        if(isset($options['date']) && !empty($options['date'])){
            $query->whereDate('created_at', '=' ,$options['date']);
        }

        if(isset($options['prize_type']) && !empty($options['prize_type'])){
            $query->where('prize_type', $options['prize_type']);
        }

        if(isset($options['region']) && !empty($options['region'])){
            $query->where('region', $options['region']);
        }

        if(isset($options['pos']) && !empty($options['pos'])){
            $query->where('pos', $options['pos']);
        }

        if($isUpdate) {
            $query = $query->lockForUpdate()->first();
        }elseif(isset($options['limit']) && !empty($options['limit'])){
            $query = $query->limit($options['limit'])->get();
        }else{
            $query->orderBy('state', 'ASC');
            $query->orderBy('expired_time', 'DESC');
            $query = $query->get();
        }

        return $query;
    }

    public static function getOrdersMultCampaign($options, $isUpdate = false){
        $query = Order::select('order.*','c.name as campaign_name')->join('campaign as c','c.id','=','order.campaign_id');
        if(isset($options['campaign_id']) && !empty($options['campaign_id'])){
            if(is_array($options['campaign_id'])){
                $query->whereIn('campaign_id', $options['campaign_id']);
            }else{
                $query->where('campaign_id', $options['campaign_id']);
            }
        }
        if(isset($options['uuid']) && !empty($options['uuid'])){
            $query->where('uuid', $options['uuid']);
        }

        if(isset($options['order_id']) && !empty($options['order_id'])){
            $query->where('id', $options['order_id']);
        }

        if(isset($options['phone']) && !empty($options['phone'])){
            $query->where('phone', $options['phone']);
        }

        if(isset($options['customer_id']) && !empty($options['customer_id'])){
            $query->where('customer_id', $options['customer_id']);
        }

        if(isset($options['date']) && !empty($options['date'])){
            $query->whereDate('created_at', '=' ,$options['date']);
        }

        if(isset($options['prize_type']) && !empty($options['prize_type'])){
            $query->where('prize_type', $options['prize_type']);
        }

        if($isUpdate){
            $query = $query->lockForUpdate()->first();
        }else{
            $query->orderBy('state', 'ASC');
            $query->orderBy('expired_time', 'DESC');
            $query = $query->get();
        }
        return $query;
    }

    public static function getOrderForSync($campaignId, $types, $refId = ''){
        $query = Order::where('campaign_id', $campaignId)
            ->where('state', 1)
            ->whereIn('prize_type', $types);

        if(!empty($refId)){
            $query->where('ref_id', $refId);
        }
        $query = $query->get();
        return $query;
    }

    public static  function insertOrder($campaignId, $customer, $uuid, $response, $prizeName){
        $codeHash = env("PRIZE_CODE", "ABCD") . '-' . $customer->id . '-' . $uuid . '-' . $response['item']['type'];
        $hash = Helpers::hashCode($codeHash, env("PRIZE_HASH_KEY", "spin12345"));

        $item = [
            'campaign_id'       => $campaignId,
            'phone'             => $customer->phone,
            'amount_voucher'    => $response['item']['voucher_value'],
            'ref_id'            => $response['item']['ref_id'],
            'state'             => 1,
            'is_send'           => 1,
            'user_agent'        => $_SERVER['HTTP_USER_AGENT'],
            'prize_type'        => $response['item']['type'],
            'prize_name'        => $prizeName,
            'uuid'              => $uuid,
            'hash_code'         => $hash,
            'expired_time'      => $response['item']['expiry'],
            'customer_id'       => $customer->id,
            'voucher_link'      => $response['item']['voucher_link'],
            'voucher_code'      => $response['item']['voucher_code'],
            'created_at'        => date('Y-m-d H:i:s', strtotime(now())),
            'updated_at'        => date('Y-m-d H:i:s', strtotime(now())),
        ];
        return Order::create($item);
    }

    public static function getWinners($campaignId, $options, $pagination = false){
        $query = Order::where('campaign_id', $campaignId)->orderBy('created_at', 'DESC');

        if($pagination){
            return $query->paginate($options['items']);
        }else{
            return $query->get();
        }
    }

    public static function getOrdersWarrior($options){
        $query = Order::select('order.*','c.name as campaign_name')
                    ->join('campaign as c','c.id','=','order.campaign_id');

        if(isset($options['campaign_id']) && !empty($options['campaign_id'])){
            if(is_array($options['campaign_id'])){
                $query->whereIn('order.campaign_id', $options['campaign_id']);
            }else{
                $query->where('order.campaign_id', $options['campaign_id']);
            }
        }
        if(isset($options['uuid']) && !empty($options['uuid'])){
            $query->where('order.uuid', $options['uuid']);
        }

        if(isset($options['phone']) && !empty($options['phone'])){
            $query->where('order.phone', $options['phone']);
        }

        if(isset($options['prize_type']) && $options['prize_type'] != ''){
            $query->where('order.prize_type', $options['prize_type']);
        }

        if(isset($options['start_date']) && !empty($options['start_date'])){
            $query->whereDate('order.created_at', '>=', $options['start_date']);
        }

        if(isset($options['end_date']) && !empty($options['end_date'])){
            $query->whereDate('order.created_at', '<=', $options['end_date']);
        }

        if(isset($options['state']) && $options['state'] !== ''){
            $query->where('order.state', $options['state']);
        }

        if(isset($options['items']) && $options['items'] > 0){
            $query = $query->paginate($options['items']);
        }else{
            if(isset($options['action']) && $options['action'] == 'export'){
                return $query;
            }
            $query = $query->get();
        }

        return $query;
    }

    public static function getTotalOrderByRegion($campaignId, $type, $region){
        return Order::where('campaign_id', $campaignId)
            ->where('prize_type', $type)
            ->where('region', $region)
            ->count();
    }

    public static function checkCodeExists($campaignId, $code){
        return Order::where('campaign_id', $campaignId)->where('uuid', $code)->count();
    }

    public static function getTotalOrderToRedeem($campaignId, $customerId){
        return Order::where('campaign_id', $campaignId)->where('customer_id', $customerId)->where('state', 0)->count();
    }

    public static function checkExistsOrder($campaignId, $customerId, $oderId, $state){
        return Order::where('campaign_id', $campaignId)
            ->where('customer_id', $customerId)
            ->where('id', $oderId)
            ->where('state', $state)
            ->count();
    }
}
