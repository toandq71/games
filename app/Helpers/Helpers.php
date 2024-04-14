<?php
# @Author: Xuan Do
# @Date:   2018-02-01T17:34:00+07:00
# @Email:  ngocxuan2255@gmail.com
# @Last modified by:   Xuan Do
# @Last modified time: 2018-03-06T17:17:42+07:00

namespace App\Helpers;

use App\Models\CampaignCustomer;
use Request;
use Carbon\Carbon;

class Helpers
{

    public static $lang = 'en';
    public static $languages = [
        'en' => ['code' => 'en', 'name' => 'English'],
        'vi' => ['code' => 'vi', 'name' => 'Việt Nam']
    ];

    public static function isWeekend($date)
    {
        return (date('N', strtotime($date)) >= 6);
    }

    public static function asset($url, $iscdn = false, $version = 'v2.54')
    {
        $fullurl = $iscdn && !empty(env('CDN_URL')) ? asset(env('CDN_URL') . $url) : asset($url);
        return strpos($url, '?') !== false ? $fullurl . '&v=' . $version : $fullurl . '?v=' . $version;
    }

    public static function trans($key, $type = 'content', $replace = [], $locale = '')
    {
        $key = \App::getLocale() == 'vi' ? $type . '.' . $key : $key;
        return __($key, $replace, $locale);
    }

    public static function checkActive($route, $page, $result = 'class=active')
    {
        return $route == $page ? $result : '';
    }

    public static function storagePath($file = '')
    {
        return storage_path($file);
    }

    public static function setLocale($lang, $default = 'en')
    {
        $locale = isset($lang) && in_array($lang, Helpers::$laguages) ? $lang : $default;
        \App::setLocale($locale);
    }

    public static function getSlug($id, $slug)
    {
        return $id . '-' . $slug;
    }

    public static function getJson($path)
    {
        if (!empty($path) && file_exists(Helpers::storagePath($path))) {
            $json = file_get_contents(Helpers::storagePath($path));
            return json_decode($json, true);
        }
        return [];
    }

    public static function searchValInArray($arr, $field, $value)
    {
        $temp = [];
        $value = Helpers::slugify($value);
        foreach ($arr as $key => $item) {
            $search = Helpers::slugify($item[$field]);
            if (strpos($search, $value) !== FALSE) {
                $temp[$key] = $item;
            }
        }
        return $temp;
    }

    public static function addMDate($date, $mins, $format = 'Y-m-d H:i:s')
    {
        return date($format, strtotime($date . $mins . " minutes"));
    }

    public static function getInterger($string)
    {
        return preg_replace('/[^0-9]/', '', $string);
    }

    public static function replaceArr($str, $arr, $isTrim = true)
    {
        foreach ($arr as $find => $replace) {
            $str = Helpers::replace($str, $find, $replace);
        }
        return $isTrim ? trim($str) : $str;
    }

    public static function replace($str, $find, $replace)
    {
        return str_replace($find, $replace, $str);
    }

    public static function isNumeric($number)
    {
        return is_numeric($number);
    }


    public static function arrKeys($arr)
    {
        return array_keys($arr);
    }

    public static function convertDate($date, $format = 'Y-m-d H:i:s')
    {
        return !empty($date) ? date($format, strtotime($date)) : $date;
    }

    public static function parseDateRange($dateRange, $hasDefault = true, $comas = '-', $format = 'Y-m-d H:i:s')
    {
        if (!empty($dateRange)) {
            $dateRange = explode($comas, $dateRange);
            if (count($dateRange) > 0) {
                return ['start_date' => Helpers::convertDate($dateRange[0], $format),
                    'end_date' => Helpers::convertDate($dateRange[1], $format)];
            }
        }
        return $hasDefault ? ['start_date' => Carbon::now()->startOfMonth()->toDateTimeString(), 'end_date' => Carbon::now()->endOfMonth()->toDateTimeString()] : array();
    }

    public static function parseYearRange($dateRange, $hasDefault = true, $comas = '-', $format = 'Y-m-d H:i:s')
    {
        if (!empty($dateRange)) {
            $dateRange = explode($comas, $dateRange);
            if (count($dateRange) > 0) {
                return ['start_date' => Helpers::convertDate($dateRange[0], $format),
                    'end_date' => Helpers::convertDate($dateRange[1], $format)];
            }
        }
        return $hasDefault ? ['start_date' => Carbon::now()->startOfYear(), 'end_date' => Carbon::now()->endOfYear()] : array();
    }

    public static function formatNumberWithLabel($land_area, $number = 2, $label = ' (Ha)')
    {
        return number_format($land_area, $number, ',', '.') . $label;
    }

    public static function jsonDecode($arr, $isArray = false, $default = [])
    {
        return !empty($arr) ? json_decode($arr, $isArray) : $default;
    }

    public static function jsonEncode($arr, $default = array())
    {
        return !empty($arr) ? json_encode($arr) : $default;
    }

    public static function prepareJson($item, $key)
    {
        $item = (array)$item;
        if (isset($item[$key]) && !empty($item[$key])) {
            return Helpers::jsonEncode($item[$key]);
        }
        return '';
    }

    public static function socialUrl($socialId, $socialType = '')
    {
        return !empty($socialId) ? 'https://facebook.com/' . $socialId : '';
    }

    public static function explode($separator, $str)
    {
        return !empty($str) ? explode($separator, $str) : [];
    }

    public static function isEmpty($item, $key)
    {
        $item = (array)$item;
        return isset($item[$key]) && !empty($item[$key]) ? false : true;
    }

    public static function getVal($item, $key, $default = '')
    {
        $item = (array)$item;
        return isset($item[$key]) && !empty($item[$key]) ? $item[$key] : $default;
    }

    public static function getInt($item, $key, $default = 0)
    {
        $item = (array)$item;
        return isset($item[$key]) ? intval($item[$key]) : $default;
    }

    public static function getObj($item, $key, $default = 0)
    {
        return isset($item->{$key}) ? $item->{$key} : $default;
    }

    public static function getConfig($config, $key, $default = 0)
    {
        $item = $config->where('key', $key)->first();
        return $item ? $item->value : $default;
    }

    public static function getObjLang($item, $key, $default = '', $lang = '')
    {
        $locale = empty($lang) ? \App::getLocale() : $lang;
        return isset($item->{$key . '_' . $locale}) && !empty($item->{$key . '_' . $locale}) ? $item->{$key . '_' . $locale} : $default;
    }

    public static function limit($text, $limit = 0, $comas = '...')
    {
        return $limit > 0 ? str_limit($text, $limit, $comas) : $text;
    }

    public static function selected($item, $val, $selected = 'selected')
    {
        return $item == $val ? $selected : '';
    }

    public static function getFisrt($array)
    {
        return count($array) > 0 ? reset($array) : array();
    }

    public static function slugify($str = NULL, $sperator = "-")
    {
        return str_slug($str, $sperator);
    }

    public static function generateTokenId($table = '')
    {
        $time = explode(' ', microtime());
        return $table . date('YmdHis') . substr($time[0], 2, 6);
    }

    public static function parseErrorToString($arr)
    {
        if (!is_array($arr) && empty($arr)) return '';

        $response = '';
        foreach ($arr as $key => $error) {
            $response .= $error . '<br />';
        }
        return rtrim($response, '<br />');
    }

    public static function getRequest($url)
    {
        $client = new \GuzzleHttp\Client(['verify' => false, 'http_errors' => false]);
        $requestGuzzle = $client->request('GET', $url, []);
        $response = $requestGuzzle->getBody()->getContents();
        return Helpers::jsonDecode($response);
    }


    public static function getIdBySlug($slug)
    {
        $ids = explode('-', $slug);
        return intval(reset($ids));
    }

    public static function isAjax()
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        }
        return false;
    }

    public static function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        }
        return $ip;
    }

    public static function createDateRange($startDate, $endDate, $format = "Y-m-d")
    {
        $begin = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $interval = new \DateInterval('P1D'); // 1 Day
        $dateRange = new \DatePeriod($begin, $interval, $end->add($interval));
        $range = [];
        foreach ($dateRange as $date) {
            $range[] = $date->format($format);
        }
        if (!$range) $range[] = $begin->format($format);
        return $range;
    }

    public static function isMobile()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (!$userAgent) return false;
        return (
            preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent) ||
            preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($userAgent, 0, 4))
        );
    }

    public static function validatePhone($phone)
    {
        $regEx = '/^(03|09|08|07|05)[0-9]{8}$/';
        return preg_match($regEx, $phone);
    }

    public static function formatPhoneNumber($number)
    {
        return preg_replace('/^0/', '84', $number);
    }


    public static function isDateInRange($startDate, $endDate, $cdate = '')
    {
        $cdate = empty($cdate) ? strtotime(date('Y-m-d')) : strtotime($cdate);
        $startT = strtotime($startDate);
        $endT = strtotime($endDate);
        return (($cdate >= $startT) && ($cdate <= $endT));

    }

    public static function checkUpdate($items, $obj)
    {
        $str = '';
        foreach ($items as $key => $val) {
            if ($obj->{$key} != $val) {
                $str .= 'update - ' . $key . ' from "' . $obj->{$key} . '" to "' . $val . '" <br/>';
            }
        }
        return $str;
    }

    public static function getCurrentMonth()
    {
        return \Carbon\Carbon::now()->month;
    }

    public static function notInSendVoucherTime()
    {
        $nowTime = strtotime(date('H:i:s'));
        if ($nowTime < strtotime('00:30:00') || $nowTime > strtotime('23:59:00')) {
            return true;
        }
        return false;
    }


    public static function notInWorkingTime($startTime, $endTime)
    {
        $nowTime = strtotime(date('H:i:s'));
        if ($nowTime > strtotime($startTime) && $nowTime < strtotime($endTime)) {
            return true;
        }
        return false;
    }

    public static function replaceCharsInString($text, $chars = 'xxxxx')
    {
        if (empty($text)) return '';
        return substr((string)$text, 0, -strlen($chars)) . $chars;
    }

    public static function convertAmount($amount)
    {
        switch ($amount) {
            case 50000 :
                return '50K';
                break;
            case 30000 :
                return '30K';
                break;
            case 20000 :
                return '20K';
                break;
        }
        return '';
    }

    public static function compareTwoDate($sdate, $edate)
    {
        try {
            $dateNow = new \DateTime($sdate);
            $endDate = new \DateTime($edate);
            if (strtotime($dateNow->format('Y-m-d')) <= strtotime($endDate->format('Y-m-d'))) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    public static function hashCode($code, $secret)
    {
        return hash_hmac('sha256', $code, $secret);
    }

    public static function getObjDate($item, $key, $default = 0)
    {
        return isset($item->{$key}) ? date('d-m-Y', strtotime($item->{$key})) : $default;
    }

    public static function formatNumber($number, $character)
    {
        if (empty($number)) return '0';
        return number_format($number, 0, '', $character);
    }

    public static function getObjStr($item, $key, $default = '')
    {
        return isset($item->{$key}) ? $item->{$key} : $default;
    }
    public static function genUuid($campaignId, $length = 10)
    {
        $seed = str_split('ABCDEFGHJKLMNPQRSTUVWXYZ' . '123456789'); // and any other characters
        shuffle($seed); // probably optional since array_is randomized; this may be redundant
        $rand = '';

        foreach (array_rand($seed, $length) as $k)
            $rand .= $seed[$k];

        // check uuid da ton tai chua, neu da ton tai uuid thi gen lai
        $result = CampaignCustomer::where('uuid', $rand)->where('campaign_id', $campaignId)->first();
        if (!empty($result)) {
            return self::genUuid(10);
        } else {
            return $rand;
        }
    }

    public static function randomOtp ()
    {
        return random_int(100000, 999999);
    }

    public static function isProduction ()
    {
        if ((env('APP_ENV') != 'production')) {
            return false;
        }
        return true;
    }
    public static function getProductDetail ($type, $telco){
        $priceId = '';
        $productId = self::getProductId($type, $telco);
        if (!empty($productId)) {
            $priceId = self::getPriceId($productId, $type);
        }
        if (empty($productId) || empty($priceId)) {
            return false;
        }
        return [$productId, $priceId];
    }

    public static function getProductId($type, $telco)
    {
        $productId = '';
        if (self::isProduction()) {
            switch (strtoupper($telco)) {
                case DetectMobileCarrier::TELCO_MOBIFONE:
                    $productId = 999;
                    break;
                case DetectMobileCarrier::TELCO_VINAPHONE:
                    $productId = 1000;
                    break;
                case DetectMobileCarrier::TELCO_VIETTEL:
                    $productId = 1001;
                    break;
                case DetectMobileCarrier::TELCO_GMOBILE:
                    $productId = 1752;
                    break;
                case DetectMobileCarrier::TELCO_VIETNAMMOBILE:
                    $productId = 1753;
                    break;
                default:
                    $productId = 0;
            }
        } else {
            $productId = 1408;
        }
        return $productId;
    }

    //////////////////
    public static function splitVoucher($value = 0)
    {
        if($value == 0) {
            return false;
        }
        $arrays = [500000,200000,100000,50000,20000,10000];
        $result = [];

        foreach($arrays as $val) {
            if($value >= $val){
                $quantity = (int) ($value / $val);
                $result[$val] = $quantity;
                $value = $value - ($val * $quantity);
            }
        }
        return $result;
    }

    public static function getProductComfort()
    {
        $productId = '';
        if (self::isProduction()) {
            $productId = 1615;
        } else {
            $productId = 1408;
        }
        return $productId;
    }
    public static function getPriceComfort($value){
        $priceId = '';
        if (self::isProduction()) {
            switch ($value) {
                case 10000:
                    $priceId = 4316;
                    break;
                case 20000:
                    $priceId = 3212;
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
                case 10000:
                    $priceId = 2754;
                    break;
                case 20000:
                    $priceId = 2755;
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

    public static function createSlug($str, $delimiter = '-'){

        $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
        return $slug;
    }

    public static function getDays($startTime, $endTime){
        $startDate = strtotime(date('Y-m-d', strtotime($startTime)));
        $endDate = strtotime(date('Y-m-d', strtotime($endTime)));
        $dateDiff = abs(($endDate - $startDate));

        return (int)(floor(($dateDiff / (60*60*24))));
    }

    public static function gen_uuid()
    {
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        return strtoupper($uuid);
    }


    public static function convertAscii ($str){
        // In thường
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);
        // In đậm
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);
        return $str;
    }

    public static function getErrorResult($code)
    {
        $msg = [
            '0001' => 'API Key is empty',
            '0002' => 'API Key is not valid',
            '1010' => 'Expiry date is not valid',
            '1011' => 'Product ID or ProductPriceID or Quantity or Expiry date was missing',
            '1012' => 'Price ID does not match with this product',
            '1013' => 'Pagination or PageSize or page was missing',
            '1014' => 'Campaign Name is missing',
            '1015' => 'Active date is not valid',
            '1016' => 'Order is not valid',
            '2001' => 'Product ID is not valid',
            '2002' => 'Category ID is not valid',
            '2003' => 'Invoice No is not valid',
            '2004' => 'Voucher code is not valid',
            '2005' => 'Email is not valid',
            '2006' => 'Phone number is not valid',
            '2007' => 'Voucher Ref ID is not valid',
            '2008' => 'Voucher Ref ID already exists',
            '2009' => 'Voucher link is not valid',
            '2010' => 'Can not post 2 params voucher link and code in one time',
            '2011' => 'Brand ID is not valid',
            '2012' => 'Total amount is not valid',
            '2013' => 'Request ID is not valid',
            '2014' => 'Product ID is not allowed',
            '9999' => 'Unknown Error Occurred',
            '3000' => 'Your api key can\'t use at this time',
            '3001' => 'Your api key has expired',
            '3002' => 'Your account has limited voucher call',
            '3003' => 'The signature is incorrect',
            '4000' => 'Otp type is not valid',
            '4001' => 'Otp type is required',
            '4002' => 'Otp password is required',
            '4003' => 'Password must be the number',
            '4004' => 'Otp require phone number',
            '4005' => 'Otp require email',
            '5001' => 'Telco is not valid',
            '5002' => 'Amount is not valid',
            '5003' => 'The merchant code is out of stock',
            '5004' => 'The price value is not valid to request merchant code',
            '5005' => 'Campaign code is not valid',
            '5006' => 'Quantity is not valid',
            '5007' => 'Price is not valid',
            '5008' => 'Campaign code not match with  this type',
            '5009' => 'Voucher Ref ID is required'
        ];

        if (array_key_exists($code, $msg)){
            return true;
        }
        return false;
    }
}
