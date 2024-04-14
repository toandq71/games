<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class CampaignCustomer extends Model
{
    protected $table = 'campaign_customer';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getCampaignCustomer($filters, $isUpdate = false){
        if (isset($filters['field']) && !empty($filters['field'])) {
            $query = CampaignCustomer::select($filters['field']);
        } else {
            $query = CampaignCustomer::select('*');
        }

        if(isset($filters['campaign_id']) && !empty($filters['campaign_id'])){
            $query->where('campaign_id', $filters['campaign_id']);
        }
        if(isset($filters['customer_id']) && !empty($filters['customer_id'])){
            $query->where('customer_id', $filters['customer_id']);
        }
        if(isset($filters['uuid']) && !empty($filters['uuid'])){
            $query->where('uuid', $filters['uuid']);
        }
        if(isset($filters['otp']) && !empty($filters['otp'])){
            $query->where('otp', $filters['otp']);
        }
        if(isset($filters['token'])){
            $query->where('token', $filters['token']);
        }
        if(isset($filters['type']) && !empty($filters['type'])){
            $query->whereIn('type', $filters['type']);
        }

        if($isUpdate){
            $query->lockForUpdate();
        }
        return $query->first();
    }


    public static function getCustomers($filters, $pagination = false){
        $query = DB::table('campaign_customer as cc')->select('cc.*', 'cb.bill_number', 'cb.date', 'cb.image', 'or.prize_name', 'cu.name as fullname', 'cu.phone', 'or.created_at as date_win', 'cu.age', 'cb.store_name')
            ->join('customer_bill as cb', 'cb.uuid', '=', 'cc.uuid')
            ->join('customer as cu', 'cu.id', '=', 'cc.customer_id')
            ->leftJoin('order as or', 'or.uuid', '=', 'cc.uuid');

        if(isset($filters['campaign_id']) && !empty($filters['campaign_id'])){
            $query->where('cc.campaign_id', $filters['campaign_id']);
        }

        if(isset($filters['start_date']) && !empty($filters['start_date'])){
            $query->whereDate('cc.created_at', '>=', $filters['start_date']);
        }

        if(isset($filters['end_date']) && !empty($filters['end_date'])){
            $query->whereDate('cc.created_at', '<=', $filters['end_date']);
        }

        if(isset($filters['type']) && $filters['type'] != ''){
            $query->where('or.prize_type', $filters['type']);
        }

        if(isset($filters['keyword']) && !empty($filters['keyword'])){
            $query->where(function ($q) use ($filters){
                $q->where('cu.phone', 'like', '%'.$filters['keyword'].'%');
                $q->orWhere('cb.bill_number', 'like', '%'.$filters['keyword'].'%');
            });
        }

        if(!$pagination){
            return $query->get();
        }else{
            return $query->paginate($filters['items']);
        }
    }

    public static function getCamCustomerForSunlight($filters, $isUpdate = false){
        $query = CampaignCustomer::select('*');

        if(isset($filters['campaign_id']) && !empty($filters['campaign_id'])){
            $query->where('campaign_id', $filters['campaign_id']);
        }
        if(isset($filters['customer_id']) && !empty($filters['customer_id'])){
            $query->where('customer_id', $filters['customer_id']);
        }
        if(isset($filters['uuid']) && !empty($filters['uuid'])){
            $query->where('uuid', $filters['uuid']);
        }

        if(isset($filters['type']) && !empty($filters['type'])){
            $query->whereIn('type', $filters['type']);
        }
        if(isset($filters['otp_verify'])){
            $query->where('otp_verify', $filters['otp_verify']);
        }

        if($isUpdate){
            $query->lockForUpdate();
        }

        return $query->get();
    }

    public static function getListCampaignCus($filters, $isUpdate = false){
        $query = CampaignCustomer::select('*');

        if(isset($filters['campaign_id']) && !empty($filters['campaign_id'])){
            $query->where('campaign_id', $filters['campaign_id']);
        }
        if(isset($filters['customer_id']) && !empty($filters['customer_id'])){
            $query->where('customer_id', $filters['customer_id']);
        }
        if(isset($filters['uuid']) && !empty($filters['uuid'])){
            $query->where('uuid', $filters['uuid']);
        }

        if(isset($filters['type']) && !empty($filters['type'])){
            $query->whereIn('type', $filters['type']);
        }
        if(isset($filters['otp'])){
            $query->where('otp', $filters['otp']);
        }
        if(isset($filters['otp_verify'])){
            $query->where('otp_verify', $filters['otp_verify']);
        }
        if(isset($filters['token'])){
            $query->where('token', $filters['token']);
        }
        if(isset($filters['state'])){
            $query->where('state', $filters['state']);
        }

        if($isUpdate){
            $query->lockForUpdate();
        }
        return $query->get();
    }

    public static function getReportPamper($filters){

        $query = DB::table('campaign_customer AS cc')->select(
            'cc.uuid',
            'or.hash_code',
            'cc.id',
            'cc.campaign_id',
            'cc.customer_id',
            'cc.remaining',
            'cc.created_at',
            'cu.name',
            'cu.code',
            'cu.phone',
            'cc.url_spin',
            'cc.state',
            'or.prize_name',
            'or.updated_at',
            'or.ref_id',
            'cp.name as campaign_name',
            'cp.slug',
            'cc.status',
            'or.state AS state_order',
            'cp.has_gift_later',
            'cp.has_resend_link_game',
            'cp.has_delete_link_game',
            'cp.has_use_phone',
            'cp.type AS campaign_type'
        )
            ->join('customer AS cu', 'cu.id', '=', 'cc.customer_id')
            ->join('campaign AS cp', 'cp.id', '=', 'cc.campaign_id')
            ->leftJoin('order AS or', 'or.uuid', '=', 'cc.uuid')
            ->whereIn('cc.campaign_id', $filters['ids']);

        if(isset($filters['phone']) && !empty($filters['phone'])){
            $query->where('cu.phone', $filters['phone']);
        }

        if(isset($filters['start_date']) && !empty($filters['start_date'])){
            $query->whereDate('or.created_at', '>=', $filters['start_date']);
        }
        if(isset($filters['end_date']) && !empty($filters['end_date'])){
            $query->whereDate('or.created_at', '<=', $filters['end_date']);
        }

        if($filters['state'] != ''){
            $query->where('cc.state', $filters['state']);
        }
        if($filters['status'] != ''){
            $query->where('cc.status', $filters['status']);
        }

        $query->orderBy('or.created_at', 'DESC');

        if($filters['pagination']){
            $query = $query->paginate($filters['items']);
        }

        return $query;
    }

    public static function getCustomersSamsung($filters, $pagination = false,$isUpdate = false){
        $query = DB::table('campaign_customer as cc')
            ->select('cc.*')
            ->join('customer as cu', 'cu.id', '=', 'cc.customer_id');

        if(isset($filters['CuCode']) && !empty($filters['CuCode'])){
            $query->where('cu.code', $filters['CuCode']);
        }
        if(isset($filters['NotCuCode']) && !empty($filters['NotCuCode'])){
            $query->where('cu.code', "<>", $filters['NotCuCode']);
        }
        if(isset($filters['CuId']) && !empty($filters['CuId'])){
            $query->where('cu.id', $filters['CuId']);
        }
        if(isset($filters['NotCuId']) && !empty($filters['NotCuId'])){
            $query->where('cu.id', "<>", $filters['NotCuId']);
        }
        if(isset($filters['CuPhone']) && !empty($filters['CuPhone'])){
            $query->where('cu.phone', $filters['CuPhone']);
        }

        if(isset($filters['otp_verify']) && !empty($filters['otp_verify'])){
            $query->where('cc.otp_verify', $filters['otp_verify']);
        }
        if(isset($filters['campaign_id']) && !empty($filters['campaign_id'])){
            $query->where('cc.campaign_id', $filters['campaign_id']);
        }

        if(isset($filters['start_date']) && !empty($filters['start_date'])){
            $query->whereDate('cc.created_at', '>=', $filters['start_date']);
        }

        if(isset($filters['end_date']) && !empty($filters['end_date'])){
            $query->whereDate('cc.created_at', '<=', $filters['end_date']);
        }

        if(isset($filters['type']) && $filters['type'] != ''){
            $query->where('or.prize_type', $filters['type']);
        }
        if($isUpdate){
            $query->lockForUpdate();
        }
        return $query->get();
    }

    public static function getReportPamperDetail($filters, $prizes){
        $strSelect = '';
        foreach ($prizes as $prize){
            if($strSelect == ''){
                $strSelect = "(SELECT COUNT(*) FROM `order` WHERE `order`.campaign_id = cc.campaign_id AND prize_type = ".$prize->type." AND `order`.uuid = cc.uuid) AS type_".$prize->type;
            }else{
                $strSelect .= ",(SELECT COUNT(*) FROM `order` WHERE `order`.campaign_id = cc.campaign_id AND prize_type = ".$prize->type." AND `order`.uuid = cc.uuid) AS type_".$prize->type;
            }
        }

        $query = DB::table('campaign_customer AS cc')
            ->select(
                'cc.uuid',
                'cc.campaign_id',
                'cc.customer_id',
                'cc.total',
                'cc.used',
                'cc.remaining',
                'cc.created_at',
                'cu.name',
                'cu.code',
                'cu.phone',
                'cc.url_spin',
                'cc.state',
                'cp.name as campaign_name',
                'cu.region',
                'cc.status',
                DB::raw($strSelect),
                'or.state AS state_order'
            )
            ->join('customer AS cu', 'cu.id', '=', 'cc.customer_id')
            ->join('campaign AS cp', 'cp.id', '=', 'cc.campaign_id')
            ->leftJoin('order AS or', 'or.uuid', '=', 'cc.uuid')
            ->whereIn('cc.campaign_id', $filters['ids']);

        if(isset($filters['phone']) && !empty($filters['phone'])){
            $query->where('cu.phone', $filters['phone']);
        }

        if(isset($filters['start_date']) && !empty($filters['start_date'])){
            $query->whereDate('or.created_at', '>=', $filters['start_date']);
        }
        if(isset($filters['end_date']) && !empty($filters['end_date'])){
            $query->whereDate('or.created_at', '<=', $filters['end_date']);
        }

        if($filters['state'] != ''){
            $query->where('cc.state', $filters['state']);
        }
        if($filters['status'] != ''){
            $query->where('cc.status', $filters['status']);
        }

        $query->orderBy('cc.created_at', 'DESC');
        $query->groupBy('cc.campaign_id', 'cc.customer_id', 'cc.uuid');

        return $query;
    }

    public static function getListCampaignCustomer($filters){
        $query = CampaignCustomer::select('campaign_customer.*','customer.code')
        ->join('customer', 'customer.id','=','campaign_customer.customer_id');

        if(isset($filters['campaign_id']) && !empty($filters['campaign_id'])){
            $query->where('campaign_customer.campaign_id', $filters['campaign_id']);
        }
        if(isset($filters['code']) && !empty($filters['code'])){
            $query->where('customer.code', $filters['code']);
        }
        if(isset($filters['uuid']) && !empty($filters['uuid'])){
            $query->where('campaign_customer.uuid', $filters['uuid']);
        }
        if(isset($filters['start_date']) && !empty($filters['start_date'])){
            $query->whereDate('campaign_customer.created_at', '>=', $filters['start_date']);
        }

        if(isset($filters['end_date']) && !empty($filters['end_date'])){
            $query->whereDate('campaign_customer.created_at', '<=', $filters['end_date']);
        }
        $query->orderBy('campaign_customer.created_at','DESC');
        if(isset($filters['pagination']) && $filters['pagination'] == true){
            return $query->paginate($filters['items']);
        }
        return $query;
    }

    public static function getReportOrder($filters){
        $query = Order::select('order.*','customer.code','c.name as campaign_name',
            \DB::raw('(select name from campaign_prize where campaign_prize.campaign_id = order.campaign_id and campaign_prize.type = order.prize_type) as prize_name')
        )
        ->join('campaign as c','c.id','=','order.campaign_id')
        ->join('customer', 'customer.id','=','order.customer_id')
        ->whereIn('order.campaign_id', $filters['ids']);
        ;

        if(isset($filters['campaign_id']) && !empty($filters['campaign_id'])){
            $query->where('order.campaign_id', $filters['campaign_id']);
        }
        if(isset($filters['code']) && !empty($filters['code'])){
            $query->where('customer.code', 'like','%'.$filters['code'].'%');
        }
        if(isset($filters['uuid']) && !empty($filters['uuid'])){
            $query->where('order.uuid', $filters['uuid']);
        }
        if(isset($filters['start_date']) && !empty($filters['start_date'])){
            $query->whereDate('order.created_at', '>=', $filters['start_date']);
        }

        if(isset($filters['end_date']) && !empty($filters['end_date'])){
            $query->whereDate('order.created_at', '<=', $filters['end_date']);
        }

        if(isset($filters['is_send'])){
            $query->whereDate('order.is_send', $filters['is_send']);
        }

        $query->orderBy('order.created_at', 'DESC');

        if(isset($filters['pagination']) && $filters['pagination'] == true){
            return $query->paginate($filters['items']);
        }
        return $query;
    }
}
