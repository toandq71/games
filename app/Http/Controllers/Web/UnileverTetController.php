<?php

namespace App\Http\Controllers\Web;

use App\Components\BaseCommon;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignCustomer;
use App\Models\CampaignPrize;
use App\Models\Customer;
use App\Models\CustomerAnswer;
use App\Models\CustomerGameItem;
use App\Models\GameItem;
use App\Models\Order;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\Response as ResponseSMS;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use DB, Validator, Cookie;

class UnileverTetController extends Controller
{
    protected $logger;
    protected $tz = 'Asia/Ho_Chi_Minh';

    public function __construct()
    {
        $logger = new Logger('UNILEVER-TET');
        $logger->pushHandler(new StreamHandler(storage_path() . "/logs/unilever-tet-" . date('Y-m-d') . ".log", Logger::INFO));
        $this->logger = $logger;
    }

    public function index(Request $request){
        $err = [];
        $phone = $request->get('phone', '');

        $setting = Setting::getAll();
        $campaignId = 14;
        $date = date('Y-m-d', strtotime(now()));

        // Kiểm tra xem campaign còn chạy không?
        if (isset($setting['CAMPAIGN_ID_U_TET']) && !empty($setting['CAMPAIGN_ID_U_TET'])) {
            $campaignId = $setting['CAMPAIGN_ID_U_TET'];
        }

        $campaign = Campaign::getCampaign($campaignId, $date, 1);

        // dd($campaign, $campaignId, $date);

        if (empty($campaign)) {
            $this->logger->info('INDEX_INFO_U_TET: CAMPAIGN ID: '.$campaignId.' KHONG TON TAI HOAC DA KET THUC');
            //redirect toi page 404
            return redirect()->route('utet.notworking');
        }

        if ($request->isMethod('post')) {
            $this->logger->info('INDEX_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaignId);

            $validator = Validator::make($request->all(), [
                'phone'     => 'required|digits:10'
            ], [
                    'phone.required'    => 'Vui lòng nhập số điện thoại',
                    'phone.digits'      => 'Số điện thoại phải gồm 10 số'
                ]
            );

            // dd(count($validator->errors()));

            if (count($validator->errors()) == 0) {
                DB::beginTransaction();
                try {
                    $paramsCus = [
                        'phone'         => $phone,
                        'campaign_id'   => $campaignId
                    ];
                    $customer = Customer::getCustomer($paramsCus, false);

                    // dd($customer, $campaignId);
                    
                    if(!empty($customer)){
                        $numMinutes = 5;
                        if(isset($setting['U_TET_EXPIRE_OTP']) && !empty($setting['U_TET_EXPIRE_OTP'])){
                            $numMinutes = $setting['U_TET_EXPIRE_OTP'];
                        }
                        $expired = Carbon::now($this->tz)->addMinutes($numMinutes)->format('Y-m-d H:i:s');
                        $otp = Helpers::randomOtp();
                        $message = str_replace(["{otp}", "{minute}"], [$otp, $numMinutes], $setting['SMS_OTP_U_TET']);

                        $filters = [
                            'campaign_id' => $campaign->id,
                            'customer_id' => $customer->id
                        ];
                        $campaignCustomer = CampaignCustomer::getListCampaignCus($filters, true);
                        if(count($campaignCustomer) > 0){
                            //đã tồn tại thông tin campaign customer
                            //lây cooki tren máy
                            $cookiePhone = Cookie::get("device_phone_{$phone}_{$campaignId}");
                            $verifyPhone = false;

                            if($cookiePhone == $phone){
                                $verifyPhone = true;
                            }
                            // kiểm tra xem customer đã xác thực OTP lần nào chưa
                            if(!empty($campaignCustomer[0]->otp) && $campaignCustomer[0]->otp_verify == 1 && $verifyPhone){
                                $this->logger->info('INDEX_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID' . $campaignId . ', SDT: ' . $phone . ' TRUOC DO DA XAC THUC THANH CONG, CHUYEN DEN TRANG CHOI GAME');
                                // redirect toi page chon game de choi
                                return redirect()->route('utet.game.choose', ['customer_id' => $campaignCustomer[0]->customer_id, 'campaign_id' => $campaignCustomer[0]->campaign_id]);
                            }else{
                                //redirect toi tang nhap otp
                                $this->logger->info('INDEX_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaignId . ', SDT: ' . $phone . ' CHUA XAC THUC HOAC MO TREN THIET BI KHAC');

                                //Kiểm tra xem otp còn hiệu lực ko?
                                $expiredTime = strtotime($campaignCustomer[0]->otp_expired);
                                $currentTime = strtotime(date('Y-m-d H:i:s', time()));

                                if($expiredTime <  $currentTime){
                                    // tiến hành gửi lại mã OTP
                                    $itemResponse = [
                                        'phone'    => $customer->phone,
                                        'message'  => $message,
                                        'provider' => $campaign->provider
                                    ];
                                    ResponseSMS::create($itemResponse);

                                    foreach ($campaignCustomer as $item){
                                        $item->otp = $otp;
                                        $item->otp_expired = $expired;
                                        $item->save();
                                    }

                                    DB::commit();
                                    if (isset($setting['U_TET_ON_OFF_SMS_OTP']) && $setting['U_TET_ON_OFF_SMS_OTP'] == 'on') {
                                        $response = Helpers::sendSMS($customer->phone, $message, $campaign->provider);
                                        $this->logger->info('INDEX_INFO_U_TET:SEND OTP AGAIN CAMPAIGN ID: ' . $campaignCustomer[0]->campaign_id . ' TO PHONE: ' . $customer->phone . ' CO RESPONSE: ' . json_encode($response));
                                    }
                                }
                                // redirect toi page otp
                                return redirect()->route('utet.otp', ['customer_id' => $customer->id, 'campaign_id' => $campaignId]);
                            }
                        }else{
                            //chưa tồn tại thông tin campaign customer
                            $uuidGame1 = Helpers::genUuid($campaign->id, 10);
                            $uuidGame2 = Helpers::genUuid($campaign->id, 10);
                            $uuidGame3 = Helpers::genUuid($campaign->id, 10);

                            $uuid = '';
                            for ($i = 1; $i <= 3; $i++){
                                if($i == 1){
                                    $uuid = $uuidGame1;
                                }else if($i == 2){
                                    $uuid = $uuidGame2;
                                }else if($i == 3){
                                    $uuid = $uuidGame3;
                                }
                                $dataGame = [
                                    'campaign_id'       => $campaign->id,
                                    'customer_id'       => $customer->id,
                                    'uuid'              => $uuid,
                                    'created_at'        => date('Y-m-d H:i:s', strtotime(now())),
                                    'updated_at'        => date('Y-m-d H:i:s', strtotime(now())),
                                    'url_spin'          => route('utet.game.choose', ['customer_id' => $customer->id, 'campaign_id' => $campaign->id]),
                                    'type'              => $i,
                                    'otp_expired'       => $expired,
                                    'otp_verify'        => 0,
                                    'otp'               => $otp,
                                    'total'             => ($i != 3) ? 1 : 0,
                                    'used'              => 0,
                                    'remaining'         => ($i != 3) ? 1 : 0,
                                    'is_send'           => 1
                                ];
                                CampaignCustomer::create($dataGame);
                            }
                            $itemResponse = [
                                'phone'    => $customer->phone,
                                'message'  => $message,
                                'provider' => $campaign->provider
                            ];
                            ResponseSMS::create($itemResponse);
                            DB::commit();

                            if (isset($setting['U_TET_ON_OFF_SMS_OTP']) && $setting['U_TET_ON_OFF_SMS_OTP'] == 'on') {
                                $response = Helpers::sendSMS($customer->phone, $message, $campaign->provider);
                                $this->logger->info('INDEX_INFO_U_TET:SEND OTP CAMPAIGN ID: ' . $campaign->id. ' TO PHONE: ' . $customer->phone . ' CO RESPONSE: ' . json_encode($response));
                            }
                            // redirect toi page otp
                            return redirect()->route('utet.otp', ['customer_id' => $customer->id, 'campaign_id' => $campaignId]);
                        }
                    }else{
                        $this->logger->info('INDEX_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: ' .$campaignId.', SDT: '.$phone.', KHONG DUOC PHEP THAM GIA CHUONG TRINH ');
                        $validator->errors()->add('invalid', 'Số điện thoại của bạn không được phép tham gia chương trình.');
                    }
                }catch (\Exception $e){
                    DB::rollBack();
                    $this->logger->error('INDEX_ERROR_U_TET: LOI EXCEPTION VOI REQUEST PARAMS: CAMPAIGN ID' . $campaignId . ', MESSAGE: ' . json_encode($e->getMessage()));
                    $validator->errors()->add('invalid', 'Dịch vụ đang bận. Vui lòng thử lại!');
                }
            }
            $err = $validator->errors();
        }

        return view('web.unilever_tet.index', [
            'err'           => $err,
            'phone'         => $phone
        ]);
    }
    public function verifyOtp(Request $request, $customer_id, $campaign_id){
        $err = '';
        $success = '';
        $phone = '';
        $second = 0;
        $item = [];

        if(!empty($campaign_id) && !empty($customer_id)){
            $filters = [
                'campaign_id'   => $campaign_id,
                'customer_id'   => $customer_id
            ];
            $campaignCustomer = CampaignCustomer::getCampaignCustomer($filters, false);
            if(empty($campaignCustomer)){
                $this->logger->info('VERIFY_OTP_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaign_id . ', CUSTOMER ID: ' . $customer_id.', KHONG TON TAI CAMPAIGN CUSTOMER');
                return redirect()->route('utet.index');
            }

            $campaign = Campaign::getCampaign($campaign_id, date('Y-m-d', strtotime(now())), 1);
            if (empty($campaign)) {
                $this->logger->info('VERIFY_OTP_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaign_id . ', CUSTOMER ID: ' . $customer_id.', CAMPAIGN KHONG HOP LE HOAC DA KET THUC');
                //redirect toi page 404
                return redirect()->route('utet.notworking');
            }

            $paramsCus = [
                'campaign_id'   => $campaign_id,
                'id'            => $customer_id
            ];
            $customer = Customer::getCustomer($paramsCus);
            if(!empty($customer)){
                $phone = $customer->phone;
            }else{
                $this->logger->info('VERIFY_OTP_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaign_id . ', CUSTOMER ID: ' . $customer_id.', KHONG TON TAI CUSTOMER');
                return redirect()->route('p-guardian.index');
            }

            $expiredTime = strtotime($campaignCustomer->otp_expired);
            $currentTime = strtotime(date('Y-m-d H:i:s', time()));
            $second = $expiredTime - $currentTime;
            $item = $campaignCustomer;

            if ($request->isMethod('post')) {
                $otp = implode('', $request->get('otp', []));
                if(!empty($otp)){
                    DB::beginTransaction();

                    try {
                        $setting = Setting::getAll();
                        $filters['otp'] = $otp;
                        $campaignCustomer = CampaignCustomer::getCampaignCustomer($filters, true);

                        if(!empty($campaignCustomer)){
                            //Kiểm tra xem otp còn hiệu lực ko?
                            if ($expiredTime > $currentTime) {
                                // cập nhật lại trạng thái xác thục thành công
                                CampaignCustomer::where('campaign_id', $campaignCustomer->campaign_id)->where('customer_id', $campaignCustomer->customer_id)->where('otp', $otp)->update(['otp_verify' => 1]);
                                DB::commit();

                                $cookie  = Cookie::make("device_phone_{$customer->phone}_{$campaignCustomer->campaign_id}", $customer->phone, 43200);
                                Cookie::queue($cookie);

                                // redirect toi trang choi game
                                $this->logger->info('VERIFY_OTP_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaign_id . ', CUSTOMER ID: ' . $customer_id.', MA OTP: '.$otp.' XAC THUC THANH CONG');

                                return redirect(route('utet.game.choose', ['customer_id' => $customer->id, 'campaign_id' => $campaign->id]));
                            }else{
                                $this->logger->info('VERIFY_OTP_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: ' . $campaign_id . ', CUSTOMER ID: ' . $customer_id.', MA OTP: '.$otp.', MA XAC THUC DA HET HAN');
                                $err = 'Mã xác thực đã hết hạn. Vui lòng bấm lấy lại mã xác thực ở bên dưới';
                            }
                        }else{
                            $this->logger->info('VERIFY_OTP_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaign_id . ', CUSTOMER ID: ' . $customer_id.', OTP: '.$otp.', MA XAC THUC KHONG HOP LE.');
                            $err = 'Mã xác thực không hợp lệ.';
                        }
                    }catch (\Exception $e){
                        DB::rollBack();
                        $this->logger->error('VERIFY_OTP_ERROR_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaign_id . ', CUSTOMER ID: ' . $customer_id.', OTP: '.$otp.', EXCEPTION MESSAGE: '.json_encode($e->getMessage()));
                        $err = 'Dịch vụ đang bận. Vui lòng thử lại!';
                    }
                }else{
                    $this->logger->info('VERIFY_OTP_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaign_id . ', CUSTOMER ID: ' . $customer_id.', OTP: '.$otp.', MA OTP EMPTY');
                    $err = 'Mã xác thực không hợp lệ.';
                }
            }
        }else{
            $this->logger->info('VERIFY_OTP_INFO_U_TET: PARAMS INVALID: CAMPAIGN ID: ' . $campaign_id . ', CUSTOMER ID: ' . $customer_id);
            return redirect()->route('utet.index');
        }

        return view('web.unilever_tet.otp', [
            'err'           => $err,
            'second'        => $second,
            'item'          => $item,
            'phone'         => $phone
        ]);
    }

    public function resendOtp(Request $request)
    {
        $campaignId = $request->get('campaign_id', '');
        $customerId = $request->get('customer_id', '');

        $msg = BaseCommon::UNKNOWN;
        $status = 0;
        $link = 0;

        if (!empty($campaignId) && !empty($customerId)) {
            if ($request->ajax()) {
                $setting = Setting::getAll();
                $numMinutes = 5;
                if (isset($setting['U_TET_EXPIRE_OTP']) && !empty($setting['U_TET_EXPIRE_OTP'])) {
                    $numMinutes = $setting['U_TET_EXPIRE_OTP'];
                }

                // get campaign
                $date = date('Y-m-d', strtotime(now()));
                $campaign = Campaign::getCampaign($campaignId, $date, 1);

                if (!empty($campaign)) {
                    DB::beginTransaction();
                    try {
                        // Get campaign customer
                        $filters = [
                            'campaign_id' => $campaignId,
                            'customer_id' => $customerId
                        ];
                        $campaignCustomer = CampaignCustomer::getListCampaignCus($filters, true);
                        if (count($campaignCustomer) > 0) {
                            // Get customer
                            $paramsCus = [
                                'campaign_id' => $campaignId,
                                'id'          => $customerId
                            ];
                            $customer = Customer::getCustomer($paramsCus);
                            if (!empty($customer)) {
                                // Kiểm tra xem otp hết hạn chưa? nếu rồi thì tiến hành gửi lại otp
                                $expiredTime = strtotime($campaignCustomer[0]->otp_expired);
                                $currentTime = strtotime(date('Y-m-d H:i:s', time()));

                                if ($expiredTime < $currentTime) {
                                    $otp = Helpers::randomOtp();
                                    $expired = Carbon::now($this->tz)->addMinutes($numMinutes)->format('Y-m-d H:i:s');
                                    $message = str_replace(["{otp}", "{minute}"], [$otp, $numMinutes], $setting['SMS_OTP_U_TET']);

                                    $this->logger->info('RESEND_OTP_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: ' . $campaignId . ', CUSTOMER ID: ' . $customerId . ', PHONE: ' . $customer->phone . ' TIEN HANH GUI LAI OTP.');
                                    // tiến hành gửi lại mã OTP

                                    foreach ($campaignCustomer as $item){
                                        $item->otp = $otp;
                                        $item->otp_expired = $expired;
                                        $item->save();
                                    }
                                    $itemResponse = [
                                        'phone' => $customer->phone,
                                        'message' => $message,
                                        'provider' => $campaign->provider
                                    ];
                                    ResponseSMS::create($itemResponse);

                                    $status = 1;
                                    DB::commit();

                                    if (isset($setting['U_TET_ON_OFF_SMS_OTP']) && $setting['U_TET_ON_OFF_SMS_OTP'] == 'on') {
                                        $response = Helpers::sendSMS($customer->phone, $message, $campaign->provider);
                                        $this->logger->info('RESEND_OTP_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: ' . $campaignId . ', CUSTOMER ID: ' . $customerId . ', GUI LAI OPT TOI PHONE: ' . $customer->phone . ' CO RESPONSE: ' . json_encode($response));
                                    }
                                    $msg = 'Lấy OTP thành công. Vui lòng kiểm tra tin nhắn';
                                    $link = route('utet.otp', ['customer_id' => $customerId, 'campaign_id' => $campaignId]);
                                } else {
                                    $status = 2;
                                    $msg = 'Mã OTP chưa hết hạn. Vui lòng kiểm tra lại.';
                                    $link = route('utet.otp', ['customer_id' => $customerId, 'campaign_id' => $campaignId]);

                                    $this->logger->info('RESEND_OTP_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: ' . $campaignId . ', CUSTOMER ID: ' . $customerId . ', PHONE: ' . $customer->phone . ' OTP CHUA HET HAN');
                                }
                            } else {
                                $status = 3;
                                $link = route('utet.index');
                                $this->logger->info('RESEND_OTP_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: ' . $campaignId . ', CUSTOMER ID: ' . $customerId . ', CUSTOMER KHONG TON TAI');
                            }
                        } else {
                            $status = 4;
                            $link = route('utet.index');
                            $this->logger->info('RESEND_OTP_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: ' . $campaignId . ', CUSTOMER ID: ' . $customerId . ', CAMPAIGN CUSTOMER KHONG TON TAI');
                        }
                    } catch (\Exception $e) {
                        DB::rollBack();

                        $status = 5;
                        $this->logger->error('RESEND_OTP_ERROR_U_TET: REQUEST PARAMS CAMPAIGN ID: ' . $campaignId . ', CUSTOMER ID: ' . $customerId . ', EXCEPTION MESSAGE: '.json_encode($e->getMessage()));
                    }
                } else {
                    $status = 6;
                    $msg = 'Chương trình đã kết thúc.';
                    $link = route('utet.index');
                    $this->logger->info('RESEND_OTP_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: ' . $campaignId . ', CUSTOMER ID: ' . $customerId . ', CHUONG TRINH DA KET THUC');
                }
            }else{
                $status = 7;
                $link = route('utet.index');
                $this->logger->info('RESEND_OTP_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: ' . $campaignId . ', CUSTOMER ID: ' . $customerId . ', METHOD NOT ALLOW');
            }
        } else {
            $status = 8;
            $link = route('utet.index');
            $this->logger->info('RESEND_OTP_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: ' . $campaignId . ', CUSTOMER ID: ' . $customerId . ', KHONG HOP LE');
        }

        $response = [
            'status'    => $status,
            'message'   => $msg,
            'link'      => $link
        ];
        $this->logger->info('RESEND_OTP_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: ' . $campaignId . ', CUSTOMER ID: ' . $customerId . ', RESEND OTP CO RESPONSE: '.json_encode($response));
        return response()->json($response);
    }


    public function chooseGame(Request $request, $customer_id, $campaign_id){
        if(!empty($campaign_id) && !empty($customer_id)){
            $filters = [
                'campaign_id'   => $campaign_id,
                'type'          => [1,2,3],
                'customer_id'   => $customer_id,
                'otp_verify'    => 1
            ];

            $campaignCustomer = CampaignCustomer::getListCampaignCus($filters, false);

            if(count($campaignCustomer) > 0){
                $this->logger->info('CHOOSE_GAME_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: '.$campaign_id.', CUSTOMER ID: '.$customer_id.' CAMPAIGN CUSTOMER HOP LE');

                $setting =  Setting::getAll();
                $onOffGame1 = 0;
                $onOffGame2 = 0;
                $onOffGame3 = 0;

                if(isset($setting['ON_OFF_GAME1_U_TET']) && $setting['ON_OFF_GAME1_U_TET'] == 'on'){
                    $onOffGame1 = 1;
                }
                if(isset($setting['ON_OFF_GAME2_U_TET']) && $setting['ON_OFF_GAME2_U_TET'] == 'on'){
                    $onOffGame2 = 1;
                }
                if(isset($setting['ON_OFF_GAME3_U_TET']) && $setting['ON_OFF_GAME3_U_TET'] == 'on'){
                    $onOffGame3 = 1;
                }

                $route = 'choosegame';
                return view('web.unilever_tet.choose_game', [
                    'route'        => $route,
                    'items'        => $campaignCustomer,
                    'onOffGame1'   => $onOffGame1,
                    'onOffGame2'   => $onOffGame2,
                    'onOffGame3'   => $onOffGame3
                ]);
            }else{
                $this->logger->info('INDEX_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: '.$campaign_id.', CUSTOMER ID: '.$customer_id.' KHONG TON LAI LINK LIST GAME');
                //đá về trang chủ
                return redirect()->route('utet.index');
            }
        }else{
            $this->logger->info('CHOOSE_GAME_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: '.$campaign_id.', CUSTOMER ID: '.$customer_id.' KHONG HOP LE');
            //redirect ve trang chu
            return redirect()->route('utet.index');
        }
    }

    public function introGame(Request $request, $customer_id, $campaign_id, $uuid){
        if(!empty($customer_id) && !empty($campaign_id) && !empty($uuid)){
            $params = [
                'campaign_id'   => $campaign_id,
                'customer_id'   => $customer_id,
                'uuid'          => $uuid
            ];
            $campaignCustomer =  CampaignCustomer::getCampaignCustomer($params);
            if(!empty($campaignCustomer)){
                if($campaignCustomer->otp_verify == 0){
                    // link chua xác thực, chuyen về trang nhập OTP
                    $this->logger->info('INTRO_GAME_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: '.$campaign_id.', CUSTOMER ID: '.$customer_id.', UUID: '.$uuid.' LINK CHUA XAC THUC, CHUYEN VE TRANG NHAP OTP');

                    return redirect()->route('utet.otp', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]);
                }else{
                    if($campaignCustomer->state == 1){
                        // link game da
                        $this->logger->info('INTRO_GAME_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: '.$campaign_id.', CUSTOMER ID: '.$customer_id.', UUID: '.$uuid.' LINK GAME DA CHOI, CHUYEN VE TRANG DANH SACH GAME');

                        return redirect()->route('utet.game.choose', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]);
                    }else{
                        return view('web.unilever_tet.game'.$campaignCustomer->type.'.cover', ['item' => $campaignCustomer]);
                    }
                }
            }else{
                $this->logger->info('INTRO_GAME_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: '.$campaign_id.', CUSTOMER ID: '.$customer_id.', UUID: '.$uuid.' CAMPAIGN CUSTOMER KHONG TON TAI');
            }
        }else{
            $this->logger->info('INTRO_GAME_INFO_U_TET: REQUEST PARAMS CAMPAIGN ID: '.$campaign_id.', CUSTOMER ID: '.$customer_id.', UUID: '.$uuid.' KHONG HOP LE');
            //đá về trang chủ
            return redirect()->route('utet.index');
        }
    }

    public function checkGame(Request $request){
        $msg = BaseCommon::UNKNOWN;
        $status = 0;
        $link = '';

        $customerId = $request->get('customer_id', '');
        $campaignId = $request->get('campaign_id', '');
        $uuid = $request->get('uuid', '');
        $type = $request->get('type', '');

        if($request->ajax()) {
            if(in_array($type, [1, 2, 3])){
                $date = date('Y-m-d', strtotime(now()));
                $campaign = Campaign::getCampaign($campaignId, $date, 1);
                if (!empty($campaign)) {
                    $filters = [
                        'campaign_id'   => $campaignId,
                        'uuid'          => $uuid,
                        'customer_id'   => $customerId,
                        'type'          => [$type]
                    ];

                    $campaignCustomer = CampaignCustomer::getCampaignCustomer($filters, false);
                    if(!empty($campaignCustomer)){
                        $isOn = false;
                        $setting = Setting::getAll();

                        if($campaignCustomer->type == 1){
                            if(isset($setting['ON_OFF_GAME1_U_TET']) && $setting['ON_OFF_GAME1_U_TET'] == 'on'){
                                $isOn = true;
                            }
                        }else if($campaignCustomer->type == 2){
                            if(isset($setting['ON_OFF_GAME2_U_TET']) && $setting['ON_OFF_GAME2_U_TET'] == 'on'){
                                $isOn = true;
                            }
                        }else if($campaignCustomer->type == 3){
                            if(isset($setting['ON_OFF_GAME3_U_TET']) && $setting['ON_OFF_GAME3_U_TET'] == 'on'){
                                $isOn = true;
                            }
                        }

                        if($isOn){
                            if($campaignCustomer->state == 0 && $campaignCustomer->otp_verify == 1){
                                $this->logger->info('CHECK_GAME_U_TET:  REQUEST PARAMS CUSTOMER ID: '.$customerId.', CAMPAIGN ID: '.$campaignId.', UUID: '.$uuid.', TYPE: '.$type.', LINK GAME HOP LE VA CHUA CHOI');

                                $status = 1;
                                $msg = '';
                                //link chơi game 1
                                $link = route('utet.game.play', [
                                    'type' => $campaignCustomer->type,
                                    'customer_id' => $campaignCustomer->customer_id,
                                    'campaign_id' => $campaignCustomer->campaign_id,
                                    'uuid' => $campaignCustomer->uuid
                                ]);
                            }else{
                                $status = 2;
                                $msg = 'Bạn đã chơi game này rồi. Vui lòng kiểm tra lại';
                                $link = route('utet.game.choose', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]);

                                $this->logger->info('CHECK_GAME_U_TET:  REQUEST PARAMS CUSTOMER ID: '.$customerId.', CAMPAIGN ID: '.$campaignId.', UUID: '.$uuid.', TYPE: '.$type.', LINK GAME DA CHOI');
                            }
                        }else{
                            $status = 3;
                            $msg = 'Game chưa diễn ra. Vui lòng kiểm tra lại';
                            $link = route('utet.game.choose', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]);

                            $this->logger->info('CHECK_GAME_U_TET:  REQUEST PARAMS CUSTOMER ID: '.$customerId.', CAMPAIGN ID: '.$campaignId.', UUID: '.$uuid.', TYPE: '.$type.', GAME CHUA DIEN RA');
                        }
                    }else{
                        $status = 4;
                        $link = route('utet.index');

                        $this->logger->info('CHECK_GAME_U_TET:  REQUEST PARAMS CUSTOMER ID: '.$customerId.', CAMPAIGN ID: '.$campaignId.', UUID: '.$uuid.', TYPE: '.$type.', KHONG TON TAI LINK GAME');
                    }
                }else{
                    $status = 5;
                    $link = route('utet.index');
                    $msg = 'Chương trình đã kết thúc.';

                    $this->logger->info('CHECK_GAME_U_TET:  REQUEST PARAMS CUSTOMER ID: '.$customerId.', CAMPAIGN ID: '.$campaignId.', UUID: '.$uuid.', TYPE: '.$type.', CHUONG TRINH DA KET THUC');
                }
            }else{
                $status = 6;
                $link = route('utet.index');

                $this->logger->info('CHECK_GAME_U_TET:  REQUEST PARAMS CUSTOMER ID: '.$customerId.', CAMPAIGN ID: '.$campaignId.', UUID: '.$uuid.', TYPE: '.$type.', TYPE KHONG HOP LE');
            }
        }
        $response = [
            'status'        => $status,
            'message'       => $msg,
            'link'          => $link
        ];
        $this->logger->info('CHECK_GAME_U_TET:  REQUEST PARAMS CUSTOMER ID: '.$customerId.', CAMPAIGN ID: '.$campaignId.', UUID: '.$uuid.', TYPE: '.$type.', CO RESPONSE: '.json_encode($response));

        return response()->json($response);
    }

    public function playGame(Request $request, $type, $customer_id, $campaign_id, $uuid){
        // dd('kkkkkkkkkkkkkkkkkkk');
        if(!empty($type) && !empty($customer_id) && !empty($campaign_id) && !empty($uuid) && in_array($type , [1, 2, 3])){
            $currentTime = strtotime(date('Y-m-d H:i:s',time()));

            DB::beginTransaction();
            try {
                $filters = [
                    'campaign_id'   => $campaign_id,
                    'uuid'          => $uuid,
                    'customer_id'   => $customer_id,
                    'type'          => [$type],
                    'otp_verify'    => 1
                ];
                $campaignCustomer = CampaignCustomer::getCampaignCustomer($filters, true);
                if(!empty($campaignCustomer)){
                    $setting = Setting::getAll();
                    if($campaignCustomer->state == 1){
                        // link game da choi
                        $this->logger->info('PLAY_GAME_INFO_U_TET: REQUEST PARAMS: TYPE: '.$type.', CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.', LINK GAME DA CHOI');

                        return redirect()->route('utet.game.choose', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]);
                    }else {
                        if($campaignCustomer->otp_verify == 0){
                            // link chua xac thuc
                            $this->logger->info('PLAY_GAME_INFO_U_TET: REQUEST PARAMS: TYPE: '.$type.', CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.', LINK GAME CHUA XAC THUC');

                            return redirect()->route('utet.otp', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]);
                        }else{
                            // dd('here', $campaignCustomer->type);
                            if($campaignCustomer->type == 1){
                                if(empty($campaignCustomer->start_time) && empty($campaignCustomer->end_time)){
                                    $numSecond = 45;
                                    if(isset($setting['U_TET_TIME_PLAY_GAME1']) && !empty($setting['U_TET_TIME_PLAY_GAME1'])){
                                        $numSecond = $setting['U_TET_TIME_PLAY_GAME1'];
                                    }
                                    $campaignCustomer->start_time = date('Y-m-d H:i:s', strtotime(now()));
                                    $campaignCustomer->end_time = date('Y-m-d H:i:s', (strtotime(now()) + $numSecond));
                                    $campaignCustomer->save();

                                    DB::commit();
                                    $this->logger->info('PLAY_GAME_INFO_U_TET: REQUEST PARAMS: TYPE: '.$type.', CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.', BAT DAU CHOI GAME');
                                }else{
                                    if(strtotime($campaignCustomer->end_time) < strtotime(now())){
                                        $this->logger->info('PLAY_GAME_INFO_U_TET: REQUEST PARAMS: TYPE: '.$type.', CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.', LINK GAME HET THOI GIAN CHOI');

                                        return redirect()->route('utet.game.result',[
                                            'customer_id'   => $campaignCustomer->customer_id,
                                            'campaign_id'   => $campaignCustomer->campaign_id,
                                            'uuid'          => $campaignCustomer->uuid
                                        ]);
                                    }
                                }
                                $endTime = strtotime($campaignCustomer->end_time);
                                $second = $endTime - $currentTime;

                                $params = [
                                    'campaign_id'   => $campaignCustomer->campaign_id,
                                    'type'          => $campaignCustomer->type
                                ];
                                $items = GameItem::getGameItem($params);
                                return view('web.unilever_tet.game'.$campaignCustomer->type.'.play', ['second' => $second,'items' => $items, 'campaignCustomer' => $campaignCustomer]);
                            }else{




                            // dd('tracnghiem================', $campaignCustomer->type);




                                $obj = [];
                                $question = [];
                                $isAnswer = false;
                                $params = [
                                    'campaign_id'   => $campaignCustomer->campaign_id,
                                    'customer_id'   => $campaignCustomer->customer_id,
                                    'group_by'      => 'question_id',
                                    'type'          => $campaignCustomer->type
                                ];
                                $customerAnswer = CustomerAnswer::getCustomerAnswer($params);
                                $numRow = count($customerAnswer);
                                if($numRow > 0){
                                    $customerAnswer = $customerAnswer->sortByDesc('end_time');
                                    $endTime = strtotime($customerAnswer[$numRow - 1]->end_time);

                                    if($endTime < $currentTime){
                                        $arrQuesId = [];
                                        foreach ($customerAnswer as $answer){
                                            array_push($arrQuesId, $answer->question_id);
                                        }
                                        $questions = Question::getQuestionNotAnswer($arrQuesId, $campaignCustomer->campaign_id, $campaignCustomer->type);
                                        if(count($questions) > 0){
                                            $question = $questions[0];
                                            $isAnswer = true;
                                        }else{
                                            $this->logger->info('PLAY_GAME_INFO_U_TET: REQUEST PARAMS: TYPE: '.$type.', CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.', DA TRA LOI HET CAC CAU HOI => CHUYEN VE TRANG KET QUA');

                                            return redirect()->route('utet.game.result',[
                                                'customer_id'   => $campaignCustomer->customer_id,
                                                'campaign_id'   => $campaignCustomer->campaign_id,
                                                'uuid'          => $campaignCustomer->uuid
                                            ]);
                                        }
                                    }else{
                                        $obj = $customerAnswer[$numRow - 1];
                                        $params = [
                                            'campaign_id'   => $campaignCustomer->campaign_id,
                                            'type'          => $campaignCustomer->type,
                                            'question_id'   => $obj->question_id
                                        ];
                                        $question = Question::getQuestionByCondition($params, true);
                                    }
                                }else{
                                    $params = [
                                        'campaign_id'   => $campaignCustomer->campaign_id,
                                        'type'          => $campaignCustomer->type
                                    ];
                                    $question = Question::getQuestionByCondition($params, true);
                                    $isAnswer = true;
                                }
                                $this->logger->info('PLAY_GAME_INFO_U_TET: REQUEST PARAMS: TYPE: '.$type.', CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.', TRA LOI CAU HOI CO QUESTION ID: '.$question->id);

                                $paramsQues = [
                                    'campaign_id'   => $campaignCustomer->campaign_id,
                                    'question_id'   => $question->id
                                ];
                                $answers =  QuestionAnswer::getQuestionAnswer($paramsQues);
                                if($isAnswer){
                                    // lay danh sach cau tra loi cua cau hoi
                                    $time = 65;
                                    // dd($setting);
                                    if($campaignCustomer->type == 2){
                                        if(isset($setting['U_TET_TIME_PLAY_QUES_GAME2']) && !empty($setting['U_TET_TIME_PLAY_QUES_GAME2'])){
                                            $time = $setting['U_TET_TIME_PLAY_QUES_GAME2']; ////////////////////////////////////////////////////
                                        }
                                    }else if($campaignCustomer->type == 3){
                                        if(isset($setting['U_TET_TIME_PLAY_QUES_GAME3']) && !empty($setting['U_TET_TIME_PLAY_QUES_GAME3'])){
                                            $time = $setting['U_TET_TIME_PLAY_QUES_GAME3'];
                                        }
                                    }
                                    $obj = CustomerAnswer::create([
                                        'question_id'   => $question->id,
                                        'customer_id'   => $campaignCustomer->customer_id,
                                        'campaign_id'   => $campaignCustomer->campaign_id,
                                        'type'          => $campaignCustomer->type,
                                        'start_time'    => date('Y-m-d H:i:s', strtotime(now())),
                                        'end_time'      => date('Y-m-d H:i:s', (strtotime(now()) + $time)),
                                        'created_at'    => date('Y-m-d H:i:s', strtotime(now())),
                                        'updated_at'    => date('Y-m-d H:i:s', strtotime(now()))
                                    ]);
                                    DB::commit();
                                    $numRow += 1;
                                }
                                // dd('yyyyyyyyyyyyyyyy');
                                $second = strtotime($obj->end_time) - $currentTime;
                                return view('web.unilever_tet.game'.$campaignCustomer->type.'.play', ['totalAnswer' => $numRow, 'second' => $second,'question' => $question, 'answers' => $answers, 'campaignCustomer' => $campaignCustomer]);
                            }
                        }
                    }
                }else{
                    $this->logger->info('PLAY_GAME_INFO_U_TET: REQUEST PARAMS: TYPE: '.$type.', CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.', LINK KHONG HOP LE');
                    return redirect()->route('utet.index');
                }
            }catch (\Exception $e){
                DB::rollBack();
                $this->logger->error('PLAY_GAME_ERROR_U_TET: REQUEST PARAMS: TYPE: '.$type.', CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.', EXCEPTION MESSAGE: '.json_encode($e->getMessage()));

                return redirect(route('utet.game.choose', ['customer_id' => $customer_id, 'campaign_id' => $campaign_id]));
            }
        }else{
            $this->logger->info('PLAY_GAME_INFO_U_TET: REQUEST PARAMS: TYPE: '.$type.', CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.', PARAMS KHONG HOP LE');
            return redirect()->route('utet.index');
        }
    }

    public function recordPlayGame(Request $request){
        $campaignId = $request->get('campaign_id', '');
        $customerId = $request->get('customer_id', '');
        $uuid = $request->get('uuid', '');
        $type = $request->get('type', '');
        $itemIds = $request->get('item_id', '');
        $questionId = $request->get('question', '');

        $msg = BaseCommon::UNKNOWN;
        $status = 0;
        $link = '';

        $this->logger->info('RECORD_PLAY_GAME_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: '.$campaignId.', CUSTOMER ID: '.$customerId.', UUID: '.$uuid.', TYPE: '.$type.', ITEM ID: '.$itemIds);

        if($request->ajax()) {
            if(!empty($campaignId) && !empty($customerId) && !empty($uuid) && !empty($type)){
                $setting =  Setting::getAll();
                $onOffGame = 0;
                $date = date('Y-m-d', strtotime(now()));

                if($type == 1){
                    if(isset($setting['ON_OFF_GAME1_U_TET']) && $setting['ON_OFF_GAME1_U_TET'] == 'on'){
                        $onOffGame = 1;
                    }
                }else if($type == 2){
                    if(isset($setting['ON_OFF_GAME2_U_TET']) && $setting['ON_OFF_GAME2_U_TET'] == 'on'){
                        $onOffGame = 1;
                    }
                }else if($type == 3){
                    if(isset($setting['ON_OFF_GAME3_U_TET']) && $setting['ON_OFF_GAME3_U_TET'] == 'on'){
                        $onOffGame = 1;
                    }
                }
                $campaign = Campaign::getCampaign($campaignId, $date, 1);
                if (!empty($campaign)) {
                    if($onOffGame == 1){
                        DB::beginTransaction();
                        try {
                            $filters = [
                                'campaign_id'   => $campaignId,
                                'uuid'          => $uuid,
                                'customer_id'   => $customerId,
                                'type'          => [$type]
                            ];
                            $campaignCustomer = CampaignCustomer::getCampaignCustomer($filters, true);
                            if(!empty($campaignCustomer)){
                                if($campaignCustomer->state == 0){
                                    if($campaignCustomer->otp_verify == 1){
                                        if($campaignCustomer->type == 1){
                                            $paramsCusGame = [
                                                'campaign_id' => $campaignCustomer->campaign_id,
                                                'customer_id' => $campaignCustomer->customer_id
                                            ];
                                            $results = CustomerGameItem::getCustomerGame($paramsCusGame, true);
                                            if (count($results) == 0) {
                                                if(!empty($itemIds)){
                                                    $arrIds = explode(',', $itemIds);
                                                    $total = 0;
                                                    foreach ($arrIds as $item_id) {
                                                        CustomerGameItem::create([
                                                            'item_id' => $item_id,
                                                            'customer_id' => $campaignCustomer->customer_id,
                                                            'campaign_id' => $campaignCustomer->campaign_id,
                                                            'created_at' => date('Y-m-d H:i:s', time()),
                                                            'updated_at' => date('Y-m-d H:i:s', time()),
                                                        ]);
                                                        $total += 1;
                                                    }
                                                    if((int)$total > 100 ){
                                                        $total = 100;
                                                    }
                                                    $campaignCustomer->percent = $total;
                                                    $campaignCustomer->save();
                                                }

                                                DB::commit();

                                                $status = 1;
                                                $msg = '';
                                                $this->logger->info('RECORD_PLAY_GAME_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaignId . ', CUSTOMER ID: ' . $customerId . ', UUID: ' . $uuid . ', TYPE: ' . $type . ', ITEM ID: ' . $itemIds . ',  INSERT CUSTOMER GAME ITEM SUCCESS');

                                                $link = route('utet.game.result', [
                                                    'customer_id' => $campaignCustomer->customer_id,
                                                    'campaign_id' => $campaignCustomer->campaign_id,
                                                    'uuid'        => $campaignCustomer->uuid
                                                ]);
                                            }else{
                                                $msg = 'Link game đã chơi. Vui lòng kiểm tra lại.';
                                                $status = 8;
                                                $link = route('utet.game.result', [
                                                    'customer_id' => $campaignCustomer->customer_id,
                                                    'campaign_id' => $campaignCustomer->campaign_id,
                                                    'uuid'        => $campaignCustomer->uuid
                                                ]);

                                                $this->logger->info('RECORD_PLAY_GAME_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaignId . ', CUSTOMER ID: ' . $customerId . ', UUID: ' . $uuid . ', TYPE: ' . $type . ', LINK GAME DA CHOI => VE TRANG KET QUA');
                                            }
                                        }else if($campaignCustomer->type == 2 || $campaignCustomer->type == 3){
                                            if(!empty($itemIds)){
                                                $params = [
                                                    'question_id' => $questionId,
                                                    'campaign_id' => $campaignId,
                                                    'id'          => $itemIds
                                                ];
                                                $answer = QuestionAnswer::getQuestionAnswer($params, true);
                                                if(!empty($answer)){
                                                    $isUpdate = CustomerAnswer::where('campaign_id', $campaignId)
                                                        ->where('customer_id', $customerId)
                                                        ->where('type', $type)
                                                        ->where('question_id', $questionId)
                                                        ->whereNull('answer_id')
                                                        ->update(['answer_id' => $itemIds, 'correct' => $answer->correct]);

                                                    $this->logger->info('RECORD_PLAY_GAME_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaignId . ', CUSTOMER ID: ' . $customerId . ', UUID: ' . $uuid . ', TYPE: ' . $type . ', QUESTION ID: '.$questionId.', ANSWER ID: '.$itemIds.', UPDATE CUSTOMER ANSWER STATUS: '.$isUpdate);

                                                    if($campaignCustomer->type == 3 && $answer->correct == 1 && $isUpdate){
                                                        $campaignCustomer->total += 1;
                                                        $campaignCustomer->remaining += 1;
                                                        $campaignCustomer->save();
                                                    }
                                                    DB::commit();
                                                }
                                            }
                                            $status = 1;
                                            $msg = '';
                                            $link = route('utet.game.play',[
                                                'type'          => $campaignCustomer->type,
                                                'customer_id'   => $campaignCustomer->customer_id,
                                                'campaign_id'   => $campaignCustomer->campaign_id,
                                                'uuid'          => $campaignCustomer->uuid
                                            ]);
                                        }
                                    }else{
                                        $status = 7;
                                        $msg = 'Link chưa xác thực. Vui lòng kiểm tra lại';
                                        $link = route('utet.otp', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]);

                                        $this->logger->info('RECORD_PLAY_GAME_INFO_U_TET:  REQUEST PARAMS CUSTOMER ID: '.$customerId.', CAMPAIGN ID: '.$campaignId.', UUID: '.$uuid.', TYPE: '.$type.', LINK CHUA XAC THUC');
                                    }
                                }else{
                                    $status = 2;
                                    $msg = 'Bạn đã chơi game này rồi. Vui lòng kiểm tra lại';
                                    $link = route('utet.game.choose', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]);

                                    $this->logger->info('RECORD_PLAY_GAME_INFO_U_TET:  REQUEST PARAMS CUSTOMER ID: '.$customerId.', CAMPAIGN ID: '.$campaignId.', UUID: '.$uuid.', TYPE: '.$type.', LINK GAME DA CHOI');
                                }
                            }else{
                                $status = 3;
                                $link = route('utet.index');

                                $this->logger->info('RECORD_PLAY_GAME_INFO_U_TET:  REQUEST PARAMS CUSTOMER ID: '.$customerId.', CAMPAIGN ID: '.$campaignId.', UUID: '.$uuid.', TYPE: '.$type.', LINK GAME KHONG HOP LE');
                            }
                        }catch (\Exception $e){
                            DB::rollBack();

                            $status = 4;
                            $link = route('utet.index');

                            $this->logger->error('RECORD_PLAY_GAME_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: '.$campaignId.', CUSTOMER ID: '.$customerId.', UUID: '.$uuid.', TYPE: '.$type.', ITEM ID: '.$itemIds.',  EXCEPTION MESSAGE: '.json_encode($e->getMessage()));
                        }
                    }else{
                        $status = 9;
                        $msg = 'Game đã đóng. Vui lòng kiểm tra lại';
                        $link = route('utet.game.choose', ['customer_id' => $customerId, 'campaign_id' => $campaignId]);

                        $this->logger->info('RECORD_PLAY_GAME_INFO_U_TET:  REQUEST PARAMS CUSTOMER ID: '.$customerId.', CAMPAIGN ID: '.$campaignId.', UUID: '.$uuid.', TYPE: '.$type.', GAME CHUA DIEN RA');
                    }
                }else{
                    $status = 5;
                    $link = route('utet.index');
                    $msg = 'Chương trình đã kết thúc.';

                    $this->logger->info('RECORD_PLAY_GAME_INFO_U_TET:  REQUEST PARAMS CUSTOMER ID: '.$customerId.', CAMPAIGN ID: '.$campaignId.', UUID: '.$uuid.', TYPE: '.$type.', CHUONG TRINH DA KET THUC');
                }
            }else{
                $status = 6;
                $link = route('utet.index');

                $this->logger->info('RECORD_PLAY_GAME_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: '.$campaignId.', CUSTOMER ID: '.$customerId.', UUID: '.$uuid.', TYPE: '.$type.', ITEM ID: '.$itemIds.',  PARAMS KHONG HOP LE');
            }
        }else{
            $this->logger->info('RECORD_PLAY_GAME_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: '.$campaignId.', CUSTOMER ID: '.$customerId.', UUID: '.$uuid.', TYPE: '.$type.', ITEM ID: '.$itemIds.',  METHOD NOT ALLOW');
        }

        $response = [
            'status'        => $status,
            'message'       => $msg,
            'link'          => $link
        ];
        $this->logger->info('RECORD_PLAY_GAME_INFO_U_TET:  REQUEST PARAMS CUSTOMER ID: '.$customerId.', CAMPAIGN ID: '.$campaignId.', UUID: '.$uuid.', TYPE: '.$type.', ITEM ID: '.$itemIds.' CO RESPONSE: '.json_encode($response));
        return response()->json($response);
    }

    public function resultGame(Request $request, $customer_id, $campaign_id, $uuid){
        if(!empty($customer_id) && !empty($campaign_id) && !empty($uuid)){
            $campaign = Campaign::getCampaignByCondition(['campaign_id' => $campaign_id]);
            if(!empty($campaign)){
                $paramsCus = [
                    'id'            => $customer_id,
                    'campaign_id'   => $campaign_id
                ];
                $customer = Customer::getCustomer($paramsCus);
                if(!empty($customer)){
                    $setting = Setting::getAll();
                    DB::beginTransaction();

                    try {
                        $filters = [
                            'campaign_id'   => $campaign_id,
                            'uuid'          => $uuid,
                            'customer_id'   => $customer_id,
                        ];
                        $campaignCustomer = CampaignCustomer::getCampaignCustomer($filters, true);
                        if(!empty($campaignCustomer)){
                            if($campaignCustomer->otp_verify == 1 && in_array($campaignCustomer->type, [1, 2, 3])){
                                $productId = $this->getProductId();
                                $amount = 20000;
                                $dataProduct = [];

                                if($campaignCustomer->state == 0){
                                    if($campaignCustomer->type == 1){
                                        $point = (int)$campaignCustomer->percent;
                                        if($point >= 50 && $point < 100){
                                            $amount = 50000;
                                        }else if($point == 100){
                                            $amount = 100000;
                                        }
                                        $priceId = $this->getPriceId($amount);
                                        if(!empty($productId) && !empty($priceId)){
                                            $item = [
                                                'productId'         => $productId,
                                                'productPriceId'    => $priceId,
                                                'quantity'          => 1,
                                                'point'             => $campaignCustomer->percent,
                                                'message'           => $setting['U_TET_SMS_VC_G1'],
                                                'prize_type'        => $amount/1000,
                                                'prize_name'        => 'Voucher '.($amount/1000).'K',
                                                'ref_id'            => 'U-TET-' . $campaignCustomer->campaign_id.'-'.$campaignCustomer->customer_id.'-'.$campaignCustomer->uuid.'-POINT-'.$point.'-'.time()

                                            ];
                                            array_push($dataProduct, $item);
                                        }
                                        $this->logger->info('GET_RESULT_INFO_U_TET: REQUEST PARAMS CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.' DATA PRODUCT: '.json_encode($dataProduct).' GAME 1 TAO VOUCHER');
                                    }else if($campaignCustomer->type == 2){
                                        $questions = Question::getQuestionAnswer($campaignCustomer->campaign_id, $campaignCustomer->type);
                                        $paramsAn = [
                                            'campaign_id'   => $campaignCustomer->campaign_id,
                                            'customer_id'   => $campaignCustomer->customer_id,
                                            'type'          => $campaignCustomer->type
                                        ];
                                        $answers = CustomerAnswer::getCustomerAnswer($paramsAn);
                                        foreach ($questions as $question){
                                            foreach ($answers as $answer){
                                                if($question->id == $answer->question_id && $answer->correct == 1){
                                                    $priceId = $this->getPriceId($question->point * 1000);
                                                    if(!empty($productId) && !empty($priceId)){
                                                        $item = [
                                                            'productId'         => $productId,
                                                            'productPriceId'    => $priceId,
                                                            'quantity'          => 1,
                                                            'point'             => $question->point,
                                                            'message'           => str_replace("{position}" , $question->position, $question->msg),
                                                            'prize_type'        => $question->point,
                                                            'prize_name'        => 'Voucher '.($question->point).'K',
                                                            'ref_id'            => 'U-TET-' . $campaignCustomer->campaign_id.'-'.$campaignCustomer->customer_id.'-'.$campaignCustomer->uuid.'-QUES-'.$question->id.'-'.$answer->id.'-POINT-'.$question->point.'-'.time()
                                                        ];
                                                        array_push($dataProduct, $item);
                                                    }
                                                }
                                            }
                                        }
                                        $this->logger->info('GET_RESULT_INFO_U_TET: REQUEST PARAMS CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.' DATA PRODUCT: '.json_encode($dataProduct).' GAME 2');
                                    }
                                    // check xem uuid nay da tao order chua
                                    $paramsOr = [
                                        'campaign_id'   => $campaignCustomer->campaign_id,
                                        'uuid'          => $campaignCustomer->uuid
                                    ];
                                    $orders = Order::getOrderByCondition($paramsOr, true);
                                    if(!empty($orders)){
                                        $dataProduct = [];
                                        $this->logger->info('GET_RESULT_INFO_U_TET: REQUEST PARAMS CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.' DATA PRODUCT: '.json_encode($dataProduct).' LINK GAME DA TON TAI ORDER');
                                    }

                                    if(in_array($campaignCustomer->type, [1, 2])){
                                        if(count($dataProduct) > 0){
                                            $days = 180;
                                            if(isset($setting['DAYS_EXPIRE_VOUCHER_U_TET']) && !empty($setting['DAYS_EXPIRE_VOUCHER_U_TET'])){
                                                $days = $setting['DAYS_EXPIRE_VOUCHER_U_TET'];
                                            }

                                            foreach ($dataProduct as $key => $object){
                                                $codeHash = env("PRIZE_CODE", "ABCD") .'-'. $customer->id.'-'.$object['point'];
                                                $hash = Helpers::hashCode($codeHash, env("PRIZE_HASH_KEY", "spin12345"));
                                                $temp = $this->getTempOrder([], $object['productId'], $object['productPriceId'], $object['quantity'], $campaign, $customer, $object['ref_id'], $days, $campaignCustomer->uuid, $hash, $object['prize_type'], $object['prize_name'], 1);

                                                if(!empty($temp)){
                                                    $order = Order::create($temp);

                                                    $message = str_replace(["{value}","{link}","{hsd}"], [Helpers::formatNumber($order->amount_voucher, ','),$order->voucher_link, date('d/m/Y', strtotime($order->expired_time))], $object['message']);
                                                    $itemResponse = [
                                                        'phone'    => $customer->phone,
                                                        'message'  => $message,
                                                        'provider' => $campaign->provider
                                                    ];
                                                    ResponseSMS::create($itemResponse);
                                                    if($campaignCustomer->state == 0){
                                                        $campaignCustomer->state = 1;
                                                        $campaignCustomer->used += 1;
                                                        $campaignCustomer->remaining -= 1;
                                                        $campaignCustomer->save();
                                                    }
                                                    DB::commit();

                                                    if(isset($setting['U_TET_ON_OFF_SMS']) && $setting['U_TET_ON_OFF_SMS'] == 'on' && !empty($message)){
                                                        $res = Helpers::sendSMS($customer->phone, $message, $campaign->provider);
                                                        $this->logger->info('GET_RESULT_INFO_U_TET: REQUEST PARAMS CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.' GUI SMSTOI SDT: '.$customer->phone.' CO TRUNG GIAI RESPONSE: '.json_encode($res));
                                                    }
                                                }
                                            }
                                        }else{
                                            if($campaignCustomer->type == 2){
                                                if($campaignCustomer->state == 0){
                                                    $paramsAnswer = [
                                                        'campaign_id'   => $campaignCustomer->campaign_id,
                                                        'customer_id'   => $campaignCustomer->customer_id,
                                                        'type'          => $campaignCustomer->type
                                                    ];
                                                    $dataAnswers = CustomerAnswer::getCustomerAnswer($paramsAnswer);
                                                    if(count($dataAnswers) > 0){
                                                        $checkAn = false;
                                                        foreach ($dataAnswers as $ans){
                                                            if($ans->correct == 1){
                                                                $checkAn = true;
                                                            }
                                                        }
                                                        if(!$checkAn){
                                                            $campaignCustomer->state = 1;
                                                            $campaignCustomer->used += 1;
                                                            $campaignCustomer->remaining -= 1;
                                                            $campaignCustomer->save();
                                                            DB::commit();
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                if($campaignCustomer->type == 1){
                                    $order = Order::where('campaign_id', $campaign_id)->where('customer_id', $customer_id)->where('uuid', $uuid)->where('phone', $customer->phone)->first();
                                    return view('web.unilever_tet.game'.$campaignCustomer->type.'.result', ['order' => $order]);

                                }else if($campaignCustomer->type == 2 || $campaignCustomer->type == 3){
                                    $questions = Question::getQuestionAnswer($campaignCustomer->campaign_id, $campaignCustomer->type);
                                    $paramsAn = [
                                        'campaign_id'   => $campaignCustomer->campaign_id,
                                        'customer_id'   => $campaignCustomer->customer_id,
                                        'type'          => $campaignCustomer->type
                                    ];
                                    $results = CustomerAnswer::getCustomerAnswer($paramsAn);

                                    $answers = [];
                                    $isAddSpin = true;

                                    $totalAnCorrect = 0;
                                    foreach ($results as $result){
                                        $result->answer = '-';
                                        if(!empty($result->answer_id)){
                                            $paramsQues = [
                                                'question_id'   => $result->question_id,
                                                'campaign_id'   => $result->campaign_id,
                                                'id'            => $result->answer_id
                                            ];
                                            $answerInfo = QuestionAnswer::getQuestionAnswer($paramsQues, true);
                                            if(!empty($answerInfo)){
                                                $result->answer = $answerInfo->answer;
                                            }
                                        }
                                        $answers[$result->question_id] = $result->toArray();
                                        if($result->correct == 1){
                                            $isAddSpin = false;
                                            $totalAnCorrect += 1;
                                        }
                                    }
                                    if($campaignCustomer->type == 3){
                                        if($campaignCustomer->total == 0 && $campaignCustomer->remaining == 0 && $campaignCustomer->state == 0 && $isAddSpin){
                                            $campaignCustomer->total = 1;
                                            $campaignCustomer->remaining = 1;
                                            $campaignCustomer->save();
                                            DB::commit();

                                            $this->logger->info('GET_RESULT_INFO_U_TET: REQUEST PARAMS CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.' GAME 3 KHONG TRA LOI DUNG CAU NAO => DUOC 1 LUOT QUAY KHUYEN KHICH');
                                        }
                                    }
                                    return view('web.unilever_tet.game'.$campaignCustomer->type.'.result', [
                                        'questions'         => $questions,
                                        'answers'           => $answers,
                                        'campaignCustomer'  => $campaignCustomer,
                                        'totalAnCorrect'    => $totalAnCorrect
                                    ]);
                                }
                            }else{
                                $this->logger->info('GET_RESULT_INFO_U_TET: REQUEST PARAMS CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.' LINK CHUA XAC THUC');
                                return redirect()->route('utet.otp', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]);
                            }
                        }else{
                            $this->logger->info('GET_RESULT_INFO_U_TET: REQUEST PARAMS CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.' LINK KHONG HOP LE');
                            return redirect()->route('utet.index');
                        }
                    }catch (\Exception $e){
                        DB::rollBack();
                        $this->logger->error('GET_RESULT_INFO_U_TET: REQUEST PARAMS CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.' EXCEPTION MESSAGE: '.json_encode($e->getMessage()));
                        return redirect()->route('utet.index');
                    }
                }else{
                    $this->logger->info('GET_RESULT_INFO_U_TET: REQUEST PARAMS CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.' KHONG TON TAI CUSTOMER');
                    return redirect()->route('utet.index');
                }
            }else{
                $this->logger->info('GET_RESULT_INFO_U_TET: REQUEST PARAMS CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.' CHUONG TRINH KHONG HOP LE');
                return redirect()->route('utet.index');
            }
        }else{
            $this->logger->info('GET_RESULT_INFO_U_TET: REQUEST PARAMS CUSTOMER ID: '.$customer_id.', CAMPAIGN ID: '.$campaign_id.', UUID: '.$uuid.' PARAMS KHONG HOP LE');
            return redirect()->route('utet.index');
        }
    }

    public function getTempOrder($productList, $productId, $priceId, $quantity, $campaign, $customer, $refId, $days, $uuid, $hash, $prizeType, $prizeName = '', $isSend = 1, $pos = 1){
        $voucherLink = '';
        $voucherCode = '';
        $expiredTime = '';
        $voucherGroup = '';
        $item = [];
        $flag = false;

        if($prizeType > 0){
            $response = BaseCommon::createVoucher($productList, $productId, $priceId, $customer->phone, $campaign->biz_order_name, $campaign->biz_api_key, $quantity, $days, $refId);
            if($response) {
                foreach ($response as $item) {
                    if (isset($item['vouchers'])) {
                        $this->logger->info('GET_RESULT_INFO_U_TET: REQUEST PARAMS CUSTOMER ID: '.$customer->id.', CAMPAIGN ID: '.$campaign->id.', UUID: '.$uuid.' TAO VOUCHER THANH CONG VOI REF ID: '.$refId);
                        if (count($item['vouchers']) >= 1) {
                            $flag = true;
                            if(count($item['vouchers']) == 1){
                                $voucherLink = $item['vouchers'][0]['voucherLink'];
                                $voucherCode = $item['vouchers'][0]['voucherCode'];
                                $expiredTime = $item['vouchers'][0]['expiryDate'];
                            }else{
                                $voucherLink = $item['groupVouchers']['voucherLink'];
                                $voucherGroup = $item['groupVouchers']['voucherLink'];
                                $voucherCode = $item['groupVouchers']['voucherLinkCode'];
                                $expiredTime = $item['vouchers'][0]['expired_date'];
                            }
                        }
                    }else{
                        $this->logger->info('GET_RESULT_INFO_U_TET: REQUEST PARAMS CUSTOMER ID: '.$customer->id.', CAMPAIGN ID: '.$campaign->id.', UUID: '.$uuid.' TAO VOUCHER THAT BAI, EMPTY VOUCHER VOI RESPONSE: '.json_encode($response));
                    }
                }
            }else{
                $this->logger->info('GET_RESULT_INFO_U_TET: REQUEST PARAMS CUSTOMER ID: '.$customer->id.', CAMPAIGN ID: '.$campaign->id.', UUID: '.$uuid.' TAO VOUCHER THAT BAI VOI RESPONSE: '.json_encode($response));
            }
        }else{
            $flag = true;
        }

        if($flag){
            $item = [
                'campaign_id'       => $campaign->id,
                'phone'             => $customer->phone,
                'amount_voucher'    => $prizeType * 1000,
                'ref_id'            => $refId,
                'state'             => 1,
                'is_send'           => $isSend,
                'user_agent'        => $_SERVER['HTTP_USER_AGENT'],
                'prize_type'        => $prizeType,
                'created_at'        => date('Y-m-d H:i:s', strtotime(now())),
                'updated_at'        => date('Y-m-d H:i:s', strtotime(now())),
                'prize_name'        => $prizeName,
                'uuid'              => $uuid,
                'hash_code'         => $hash,
                'expired_time'      => (!empty($expiredTime)) ? $expiredTime: NULL,
                'customer_id'       => $customer->id,
                'voucher_link'      => $voucherLink,
                'voucher_code'      => $voucherCode,
                'voucher_group'     => $voucherGroup,
                'pos'               => $pos
            ];
        }
        return $item;
    }

    public function getProductId(){
        $productId = '';
        if (Helpers::isProduction()) {
            $productId = 1615;
        } else {
            $productId = 1408;
        }
        return $productId;
    }

    public function getPriceId($value){
        $priceId = '';
        if (Helpers::isProduction()) {
            switch ($value) {
                case 20000:
                    $priceId = 3212;
                    break;
                case 30000:
                    $priceId = 3294;
                    break;
                case 50000:
                    $priceId = 3170;
                    break;
                case 100000:
                    $priceId = 3168;
                    break;
            }
        }else{
            switch ($value) {
                case 20000:
                    $priceId = 2755;
                    break;
                case 30000:
                    $priceId = 2756;
                    break;
                case 50000:
                    $priceId = 2710;
                    break;
                case 100000:
                    $priceId = 2711;
                    break;
            }
        }
        return $priceId;
    }

    public function getSpin(Request $request, $customer_id, $campaign_id, $uuid){
        $this->logger->info('GET_SPIN_INFO_U_TET: REQUEST PARAMS:  CAMPAIGN ID: '.$campaign_id.', CUSTOMER ID: '.$customer_id.', UUID: '.$uuid);
        $setting = Setting::getAll();

        $campaignId = 14;
        if(isset($setting['CAMPAIGN_ID_U_TET']) && !empty($setting['CAMPAIGN_ID_U_TET'])){
            $campaignId = $setting['CAMPAIGN_ID_U_TET'];
        }

        if($campaign_id != $campaignId){
            $this->logger->info('GET_SPIN_INFO_U_TET: REQUEST PARAMS:  CAMPAIGN ID: '.$campaign_id.', CUSTOMER ID: '.$customer_id.', UUID: '.$uuid.' CHUONG TRINH KHONG HOP LE');
            return redirect()->route('utet.index');
        }

        $date = date('Y-m-d', strtotime(now()));
        $campaign = Campaign::getCampaign($campaignId, $date, 1);

        if (empty($campaign)) {
            $this->logger->info('GET_SPIN_INFO_U_TET: REQUEST PARAMS:  CAMPAIGN ID: '.$campaign_id.', CUSTOMER ID: '.$customer_id.', UUID: '.$uuid.', CHUONG TRINH DA KET THUC HOAC KHONG HOP LE');
            return redirect()->route('utet.index');
        }

        $filters = [
            'campaign_id'   => $campaign->id,
            'uuid'          => $uuid,
            'customer_id'   => $customer_id
        ];
        $campaignCustomer = CampaignCustomer::getCampaignCustomer($filters);
        if(!empty($campaignCustomer) && $campaignCustomer->type == 3){
            if($campaignCustomer->otp_verify == 1){
                $prizes = CampaignPrize::getPrizeSpinU($campaignId, [], 1, false);
                return view('web.unilever_tet.game3.spin', ['campaignCustomer' => $campaignCustomer, 'prizes' => $prizes, 'campaign' => $campaign]);
            }else{
                $this->logger->info('GET_SPIN_INFO_U_TET: REQUEST PARAMS:  CAMPAIGN ID: '.$campaign_id.', CUSTOMER ID: '.$customer_id.', UUID: '.$uuid.', LINK QUAY CHUA XAC THUC => VE TRANG OTP');
                return redirect()->route('utet.otp', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]);
            }
        }else{
            $this->logger->info('GET_SPIN_INFO_U_TET: REQUEST PARAMS:  CAMPAIGN ID: '.$campaign_id.', CUSTOMER ID: '.$customer_id.', UUID: '.$uuid.', LINK QUAY KHONG HOP LE');
            return redirect()->route('utet.index');
        }
    }

    public function processSpin(Request $request){
        $campaign_id = $request->get('campaign_id', '');
        $customerId = $request->get('customer_id', '');
        $uuid = $request->get('uuid', '');

        $this->logger->info('PROCESS_SPIN_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaign_id .', UUID: '.$uuid.' CUSTOMER ID: '.$customerId);

        $msg = BaseCommon::UNKNOWN;
        $number = 5;
        $status = 0;
        $data = [];
        $checkSum = '';
        $link = '';

        if ($request->ajax() && !empty($campaign_id)) {
            $setting = Setting::getAll();
            $campaignId = 14;
            if(isset($setting['CAMPAIGN_ID_U_TET']) && !empty($setting['CAMPAIGN_ID_U_TET'])){
                $campaignId = $setting['CAMPAIGN_ID_U_TET'];
            }

            if($campaign_id == $campaignId){
                $date = date('Y-m-d', strtotime(now()));
                $campaign = Campaign::getCampaign($campaignId, $date, 1);

                if (!empty($campaign)) {
                    if(isset($setting['ON_OFF_GAME3_U_TET']) && $setting['ON_OFF_GAME3_U_TET'] == 'on'){
                        DB::beginTransaction();
                        try {
                            $filters = [
                                'campaign_id'   => $campaign->id,
                                'customer_id'   => $customerId,
                                'uuid'          => $uuid
                            ];
                            $campaignCustomer = CampaignCustomer::getCampaignCustomer($filters, true);
                            if(!empty($campaignCustomer)){
                                if($campaignCustomer->otp_verify == 1){
                                    if($campaignCustomer->type == 3){
                                        //Kiểm tra xem link còn lượt quay không
                                        if($campaignCustomer->remaining > 0){
                                            $paramsCus = [
                                                'campaign_id'   => $campaignCustomer->campaign_id,
                                                'id'            => $campaignCustomer->customer_id,
                                            ];
                                            $customer = Customer::getCustomer($paramsCus);
                                            if(!empty($customer)){
                                                if(isset($setting['NUM_RECURSIVE_U_TET']) && !empty($setting['NUM_RECURSIVE_U_TET'])){
                                                    $number = $setting['NUM_RECURSIVE_U_TET'];
                                                }
                                                // lay danh sach customer theo NPP
                                                $listCustomer = Customer::where('campaign_id', $campaignId)->where('region', $customer->region)->get();
                                                $arrCusIds = [];
                                                if(count($listCustomer)){
                                                    foreach ($listCustomer as $ob){
                                                        array_push($arrCusIds, $ob->id);
                                                    }
                                                }
                                                // goi ham xu ly random
                                                $object = $this->processRandomPrize($arrCusIds, $customer, $campaignCustomer, $number, 1);
                                                if(!empty($object)){
                                                    $this->logger->info('PROCESS_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaignId . ', UUID: ' . $uuid . ', NPP: '.$customer->region.', LUOT QUAY THU: '.($campaignCustomer->used + 1).' DUOC GIAI FILNAL: '.json_encode($object));

                                                    $prize = CampaignPrize::getPrizeForSpin($object->id, $object->campaign_id, $object->type);
                                                    if (!empty($prize)) {
                                                        $data = $prize;
                                                        $days = 180;
                                                        if(isset($setting['DAYS_EXPIRE_VOUCHER_U_TET']) && !empty($setting['DAYS_EXPIRE_VOUCHER_U_TET'])){
                                                            $days = $setting['DAYS_EXPIRE_VOUCHER_U_TET'];
                                                        }
                                                        $codeHash = env("PRIZE_CODE", "ABCD") . $uuid.($campaignCustomer->used + 1);
                                                        $hash = Helpers::hashCode($codeHash, env("PRIZE_HASH_KEY", "spin12345"));

                                                        $temp = $this->getTempOrder(
                                                            [],
                                                            $prize->product_id,
                                                            $prize->price_id,
                                                            1,
                                                            $campaign,
                                                            $customer,
                                                            $refId = 'U-TET-UUID-' . $campaignCustomer->uuid .'-SPIN-'.($campaignCustomer->used + 1).'-'.$prize->type.'-'.time(),
                                                            $days,
                                                            $campaignCustomer->uuid,
                                                            $hash,
                                                            $prize->type,
                                                            $prize->name,
                                                            0,
                                                            ($campaignCustomer->used + 1)
                                                        );
                                                        if(!empty($temp)) {
                                                            if($prize->type != 0){
                                                                $prize->remaining = $prize->remaining - 1;
                                                            }
                                                            $prize->used = $prize->used + 1;
                                                            $prize->save();

                                                            $order = Order::create($temp);

                                                            $campaignCustomer->used += 1;
                                                            $campaignCustomer->remaining -= 1;

                                                            if($campaignCustomer->remaining == 0){
                                                                $campaignCustomer->state = 1;
                                                            }
                                                            $campaignCustomer->save();

                                                            $status = 1;
                                                            DB::commit();

                                                            $msg = '';
                                                            $link = route('utet.game3.spin.result', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaign->id, 'order_id' => $order->id, 'uuid' => $uuid]);
                                                        }
                                                    }else{
                                                        $this->logger->info('PROCESS_SPIN_INFO_U_TET: PARAMS: CUSTOMER ID: ' . $customerId . ', CAMPAIGN ID: ' . $campaignId . ', UUID: ' . $uuid . ' SO LUONG GIAI CO TYPE: '.$object->type.' DA HET');
                                                        $status = 2;
                                                    }
                                                }else{
                                                    //Khong random ra duoc giai
                                                    $this->logger->info('PROCESS_SPIN_INFO_U_TET: PARAMS: CUSTOMER ID: ' . $customerId . ', CAMPAIGN ID: ' . $campaignId . ', UUID: ' . $uuid . ', LOI KHONG RANDOM RA DUOC GIAI');
                                                    $status = 3;
                                                }
                                            }else{
                                                $this->logger->info('PROCESS_SPIN_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaignId .', CUSTOMER ID: '.$customerId.', UUID: '.$uuid.' CUSTOMER KHONG THUOC CHUONG TRINH NAY');
                                                $status = 4;
                                                $link = route('utet.index');
                                            }
                                        }else{
                                            $this->logger->info('PROCESS_SPIN_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaignId .', CUSTOMER ID: '.$customerId.', UUID: '.$uuid.' LINK HET LUOT QUAY');
                                            $status = 5;
                                            $msg = "Bạn đã hết lượt quay.";
                                            $link = route('utet.game.choose', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]);
                                        }
                                    }else{
                                        $this->logger->info('PROCESS_SPIN_INFO_U_TET: REQUEST WITH PARAMS: CAMPAIGN ID: ' . $campaignId .', CUSTOMER ID: '.$customerId.', UUID: '.$uuid.' LINK QUAY KHONG DUNG LOẠI TYPE = 3');
                                        $status = 6;
                                        $link = route('utet.game.choose', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]);
                                    }
                                }else{
                                    $this->logger->info('PROCESS_SPIN_INFO_U_TET: REQUEST WITH PARAMS: CAMPAIGN ID: ' . $campaignId .', CUSTOMER ID: '.$customerId.', UUID: '.$uuid.' LINK CHUA XAC THUC');
                                    $status = 7;
                                    $link = route('utet.otp', ['customer_id' => $campaignCustomer->customer_id, 'campaign_id' => $campaignCustomer->campaign_id]);
                                }
                            }else{
                                $this->logger->info('PROCESS_SPIN_INFO_U_TET: REQUEST WITH PARAMS: CAMPAIGN ID: ' . $campaignId .', CUSTOMER ID: '.$customerId.', UUID: '.$uuid.' LINK QUAY KHONG HOP LE');
                                $status = 8;
                                $link = route('utet.index');
                            }
                        }catch (\Exception $e){
                            $this->logger->error('PROCESS_SPIN_ERROR_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaign_id .', UUID: '.$uuid.' CUSTOMER ID: '.$customerId.', EXCEPTION MESSAGE: ' . json_encode($e->getMessage()));
                            DB::rollBack();
                            $status = 9;
                        }
                    }else {
                        $this->logger->info('PROCESS_SPIN_INFO_U_TET: REQUEST PARAMS: CAMPAIGN ID: ' . $campaign_id .', UUID: '.$uuid.' CUSTOMER ID: '.$customerId.', LINK GAME CHUA MO');
                        $status = 10;
                        $msg = 'Game chưa diễn ra. Vui lòng kiểm tra lại.';
                        $link = route('utet.game.choose', ['customer_id' => $customerId, 'campaign_id' => $campaignId]);
                    }
                }else{
                    $this->logger->info('PROCESS_SPIN_INFO_U_TET: CAMPAIGN ID: ' . $campaignId . ' KHONG TON TAI HOAC DA KET THUC');
                    $status = 11;
                    $msg = 'Chương trình đã kết thúc.';
                    $link = route('utet.index');
                }
            }else{
                $status = 12;
                $msg = 'Chương trình không hợp lệ. Vui lòng kiểm tra lại.';
                $link = route('utet.index');
            }
        }

        $response = [
            'status'        => $status,
            'message'       => $msg,
            'data'          => $data,
            'check_sum'     => $checkSum,
            'link'          => $link
        ];
        $this->logger->info('PROCESS_SPIN_INFO_U_TET: REQUEST WITH PARAMS: CAMPAIGN ID: ' . $campaign_id .', UUID: '.$uuid.' CUSTOMER ID: '.$customerId.', STATUS: '.$status.', RESPONSE RETURN: '.json_encode($response));
        return response()->json($response);
    }

    public function processRandomPrize($customerIds, $customer, $campaignCustomer, $number, $loop)
    {
        $this->logger->info('PROCESS_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaignCustomer->campaign_id . ', UUID: ' . $campaignCustomer->uuid . ' LUOT QUAY THU: '.($campaignCustomer->used + 1).' DI RANDOM GIAI');

        $minPrize = [];
        $data = [];
        $percent = 0;
        $types = [];
        $prizeAvailable = [];

        // check xem NPP da co customer nao trung giai 500k chua
        $checkLimitPrize = Order::checkQuotaPrizeU($campaignCustomer->campaign_id, $customerIds , 500);
        if($checkLimitPrize){
            $types = [500];
            $this->logger->info('PROCESS_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaignCustomer->campaign_id . ', UUID: ' . $campaignCustomer->uuid . ', CUSTOMER ID: '.$customer->id.', CUSTOMER PHONE: '.$customer->phone.', NPP: '.$customer->region.', NPP TRUOC DO DA CO CUSTOMER TRUNG GIAI 500K.');
        }
        // lay danh sach giai theo type code = 1
        $prizes = CampaignPrize::getPrizeSpinU($campaignCustomer->campaign_id, $types, 1, true);

        foreach ($prizes as $prize){
            if($prize->type == 0){
                $minPrize = $prize;
            }
            if((int)$campaignCustomer->used > 0) {
                $prizeAvailable[$prize->type] = $prize;
            }
        }
        if((int)$campaignCustomer->used > 0){
            $turns = (int)($campaignCustomer->used + 1);
            $prizes = CampaignPrize::getPrizeSpinU($campaignCustomer->campaign_id, $types, $turns, false);
            $this->logger->info('PROCESS_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaignCustomer->campaign_id . ', UUID: ' . $campaignCustomer->uuid . ' LUOT QUAY THU: '.($campaignCustomer->used + 1).' CO TYPES: '.json_encode($types).', TURNS: '.$turns.' PRIZES: '.json_encode($prizes));
        }

        $strType = '';
        foreach ($prizes as $item){
            if(!empty($strType)){
                $strType = $strType.'|'.$item->type;
            }else{
                $strType = $item->type;
            }
        }
        $this->logger->info('PROCESS_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaignCustomer->campaign_id . ', UUID: ' . $campaignCustomer->uuid . ' LUOT QUAY THU: '.($campaignCustomer->used + 1).' DI RANDOM GIAI, LIST PRIZE TYPE: '.$strType);

        $rand = (round((float)rand() / (float)getrandmax(), 4)) * 100;
        if (count($prizes) > 0) {
            // random trong pham vi nhung giai co gia tri
            $this->logger->info('PROCESS_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaignCustomer->campaign_id . ', UUID: ' . $campaignCustomer->uuid . ' LUOT QUAY THU: ' . ($campaignCustomer->used + 1) . ', DI RANDOM DUOC RAND: ' . $rand);

            //tinh rate de xem random ra giai gi
            foreach ($prizes as $item) {
                if($campaignCustomer->used == 0){
                    $this->logger->info('PROCESS_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaignCustomer->campaign_id . ', UUID: ' . $campaignCustomer->uuid . ' LUOT QUAY THU: ' . ($campaignCustomer->used + 1) . ', DI RANDOM. LOAI GIAI: ' . $item->name . ' CO RATE: ' . $item->rate . ' VA REMAINING: ' . $item->remaining);
                    if ($item->remaining > 0 && (float)$item->rate > 0) {
                        $percent += $item->rate;
                        if ((float)($rand) <= (float)($percent)) {
                            $this->logger->info('PROCESS_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaignCustomer->campaign_id . ', UUID: ' . $campaignCustomer->uuid . ' LUOT QUAY THU: ' . ($campaignCustomer->used + 1) . ', DI RANDOM DUOC GIAI: ' . $item->name . ' VOI PERCENT FINAL: ' . $percent);
                            $data = $item;
                            break;
                        }
                    }
                }else{
                    $this->logger->info('PROCESS_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaignCustomer->campaign_id . ', UUID: ' . $campaignCustomer->uuid . ' LUOT QUAY THU: ' . ($campaignCustomer->used + 1) . ', DI RANDOM. LOAI GIAI: ' . $item->name . ' CO RATE: ' . $item->rate . ', TYPE CODE 1 VOI PRIZE TYPE: ' . $item->type.' VA REMAINING: '.$prizeAvailable[$item->type]->remaining);

                    if($prizeAvailable[$item->type]->remaining > 0){
                        $this->logger->info('PROCESS_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaignCustomer->campaign_id . ', UUID: ' . $campaignCustomer->uuid . ' LUOT QUAY THU: ' . ($campaignCustomer->used + 1) . ', DI RANDOM. LOAI GIAI: ' . $item->name . ' CO RATE: ' . $item->rate . ' VA REMAINING: ' . $prizeAvailable[$item->type]->remaining);
                        $percent += $item->rate;
                        if ((float)($rand) <= (float)($percent)) {
                            $this->logger->info('PROCESS_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaignCustomer->campaign_id . ', UUID: ' . $campaignCustomer->uuid . ' LUOT QUAY THU: ' . ($campaignCustomer->used + 1) . ', DI RANDOM DUOC GIAI: ' . $item->name . ' CO RATE: ' . $item->rate . ' VOI PERCENT FINAL: ' . $percent);
                            $data = $prizeAvailable[$item->type];
                            break;
                        }
                    }
                }
            }
        }else{
            $this->logger->info('PROCESS_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaignCustomer->campaign_id . ', UUID: ' . $campaignCustomer->uuid . ' LUOT QUAY THU: '.($campaignCustomer->used + 1).' CHUONG TRINH CHUA SET CO CAU GIAI');
        }

        if(empty($data)){
            $loop++;
            if ($loop <= (int)$number) {
                $this->logger->info('PROCESS_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaignCustomer->campaign_id . ', UUID: ' . $campaignCustomer->uuid . ' LUOT QUAY THU: '.($campaignCustomer->used + 1).' CO RANDOM: '.$rand.' VA PERCENT: '.$percent.' DI RANDOM LAI LAN: '.$loop);
                return $this->processRandomPrize($customerIds, $customer, $campaignCustomer, $number, $loop);
            }else{
                $this->logger->info('PROCESS_SPIN_INFO_PANTENE: PARAMS: CAMPAIGN ID: ' . $campaignCustomer->campaign_id . ', UUID: ' . $campaignCustomer->uuid . ' LUOT QUAY THU: '.($campaignCustomer->used + 1).' CO RANDOM: '.$rand.' VA PERCENT: '.$percent.' HET 5 LAN RANDOM LAI => GIAI MAY MAN');
                return $minPrize;
            }
        }else{
            return $data;
        }
    }

    public function resultSpin(Request $request, $customer_id, $campaign_id, $order_id, $uuid){
        $this->logger->info('RESULT_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaign_id . ' , ORDER ID: ' . $order_id . ', UUID: ' . $uuid.', CUSTOMER ID: '.$customer_id);

        if (!empty($campaign_id) && !empty($order_id) && !empty($uuid) && !empty($customer_id)) {
            $setting = Setting::getAll();
            $options = [
                'uuid' => $uuid,
                'campaign_id' => $campaign_id,
                'order_id' => $order_id
            ];
            DB::beginTransaction();
            try {
                $order = Order::getOrderByCondition($options, true);
                if (!empty($order)) {
                    $prize = CampaignPrize::getPrize($order->campaign_id, $order->prize_type, false);
                    $campaign = Campaign::getCampaignById($order->campaign_id);

                    $filters = [
                        'campaign_id'   => $campaign->id,
                        'customer_id'   => $order->customer_id,
                        'uuid'          => $uuid,
                        'type'          => [3]
                    ];
                    $campaignCustomer = CampaignCustomer::getCampaignCustomer($filters);

                    if ($order->is_send == 0 && $order->prize_type > 0 && !empty($campaignCustomer)) {
                        // Gửi sms nội dung trúng giải tới sdt đã quay
                        $message = str_replace(["{value}", "{number}", "{link}", "{hsd}"], [ Helpers::formatNumber($order->amount_voucher, ','), ($order->pos), $order->voucher_link, date('d/m/Y', strtotime($order->expired_time))], $prize->msg_sms);

                        if (isset($setting['U_TET_ON_OFF_SMS']) && $setting['U_TET_ON_OFF_SMS'] == 'on') {
                            $res = Helpers::sendSMS($order->phone, $message, $campaign->provider);
                            $this->logger->info('RESULT_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaign_id . ' , ORDER ID: ' . $order_id . ', UUID: ' . $uuid.' GUI TIN TRUNG GIA TOI SDT: '.$order->phone.', VOI NOI DUNG: '.$message.' CO STATUS: '.json_encode($res['stt']));
                        }
                        $item = [
                            'phone'         => $order->phone,
                            'message'       => $message,
                            'provider'      => $campaign->provider,
                            'created_at'    => date('Y-m-d H:i:s', strtotime(now())),
                            'updated_at'    => date('Y-m-d H:i:s', strtotime(now()))
                        ];
                        ResponseSMS::create($item);

                        $order->is_send = 1;
                        $order->save();
                        DB::commit();
                    }else{
                        if($order->is_send == 0 && $order->prize_type == 0){
                            $order->is_send = 1;
                            $order->save();
                            DB::commit();
                        }
                    }
                    return view('web.unilever_tet.game3.result_spin',
                        [
                            'prize'             => $prize,
                            'order'             => $order,
                            'campaignCustomer'  => $campaignCustomer,
                            'campaign'          => $campaign
                        ]);
                } else {
                    $this->logger->info('RESULT_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaign_id . ' , ORDER ID: ' . $order_id . ', UUID: ' . $uuid . ' KHONG TON TAI ORDER');
                    //redirect toi page 404
                    return redirect()->route('utet.index');
                }
            } catch (\Exception $e) {
                $this->logger->info('RESULT_SPIN_INFO_U_TET: PARAMS: CAMPAIGN ID: ' . $campaign_id . ' , ORDER ID: ' . $order_id . ', UUID: ' . $uuid . ' LOI EXCEPTION VOI MESSAGE: ' . json_encode($e->getMessage()));
                DB::rollBack();
                return redirect()->route('utet.index');
            }
        } else {
            $this->logger->info('RESULT_SPIN_INFO_U_TET: PARAMS INVALID: CAMPAIGN ID: ' . $campaign_id . ' , ORDER ID: ' . $order_id . ', UUID: ' . $uuid);
            //redirect toi page 404
            return redirect()->route('utet.index');
        }
    }
}
