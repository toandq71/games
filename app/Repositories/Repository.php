<?php


namespace App\Repositories;


use App\Models\Campaign;
use App\Models\CampaignCustomer;
use App\Models\CampaignPrize;
use App\Models\Customer;
use App\Models\Order;

class Repository
{
    public function runningCampaign($campaignId)
    {
        $date = date('Y-m-d', strtotime(now()));
        $options = [
            'date'          => $date,
            'campaign_id'   => $campaignId,
            'is_active'     => 1
        ];
        return Campaign::getCampaignByCondition($options);
    }

    public function getGamingRoundByUuidAndCampaignId($uuid, $campaignId)
    {
        $filters = [
            'campaign_id'   => $campaignId,
            'uuid'          => $uuid
        ];

        return CampaignCustomer::getCampaignCustomer($filters);
    }

    public function getOrdersByUuidAndCampaignId($uuid, $campaignId)
    {
        $paramOrder = [
            'campaign_id'   => $campaignId,
            'uuid'          => $uuid
        ];
        return Order::getOrderByCondition($paramOrder);
    }

    public function customerJoinedCampaign($campaignId, $customerId)
    {
        $paramCus = [
            'campaign_id'   => $campaignId,
            'id'            => $customerId
        ];

        return Customer::getCustomer($paramCus);
    }

    public function prizesOfCampaign($campaignId)
    {
        return CampaignPrize::getPrizeByCampaign($campaignId);
    }
}
