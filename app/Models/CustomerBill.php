<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerBill extends Model
{
    protected $table = 'customer_bill';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getCustomerBill($filters, $isUpdate = true){
        $query= CustomerBill::select('*');

        if(isset($filters['customer_id']) && !empty($filters['customer_id'])){
            $query->where('customer_id', $filters['customer_id']);
        }
        if(isset($filters['bill_number']) && !empty($filters['bill_number'])){
            $query->where('bill_number', $filters['bill_number']);
        }
        if(isset($filters['campaign_id']) && !empty($filters['campaign_id'])){
            $query->where('campaign_id', $filters['campaign_id']);
        }
        if(isset($filters['uuid']) && !empty($filters['uuid'])){
            $query->where('uuid', $filters['uuid']);
        }
        if(isset($filters['date']) && !empty($filters['date'])){
            $query->whereDate('date', '=', $filters['date']);
        }

        if($isUpdate){
            $query = $query->lockForUpdate();
        }
        $query = $query->first();
        return $query;
    }

    public static function getListCustomerBill($filters){
        $query= CustomerBill::select('*');

        if(isset($filters['customer_id']) && !empty($filters['customer_id'])){
            $query->where('customer_id', $filters['customer_id']);
        }

        if(isset($filters['campaign_id']) && !empty($filters['campaign_id'])){
            $query->where('campaign_id', $filters['campaign_id']);
        }
        if(isset($filters['uuid']) && !empty($filters['uuid'])){
            $query->where('uuid', $filters['uuid']);
        }
        $query->orderBy('date', 'ASC');
        $query = $query->get();

        return $query;
    }
}
