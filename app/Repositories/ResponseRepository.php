<?php


namespace App\Repositories;
use App\Models\Response;

class ResponseRepository extends Repository
{
    public function insertResponse($phone, $campaign, $message, $status){
        $item = [
            'phone'         => $phone,
            'message'       => $message,
            'campaign_id'   => $campaign->id,
            'provider'      => $campaign->provider,
            'status'        => $status,
            'created_at'    => date('Y-m-d H:i:s', strtotime(now())),
            'updated_at'    => date('Y-m-d H:i:s', strtotime(now()))
        ];
        return Response::create($item);
    }
}
