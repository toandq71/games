<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $status  = -1;
    protected $message = "";
    protected $data = array();

    protected $pTitle;
    protected $headers;
    protected $route;
    protected $compact = array();

    public function customView($viewName, $arr = [] ) {

        $this->setSession('ROUTENAME', $this->route);
        $result =  array_merge($arr, $this->compact);
        return view($viewName)->with($result);
    }

    public function setCookie($key , $value) {
        \Illuminate\Support\Facades\Cookie::queue($key, $value);
    }

    public function setSession($key , $value) {
        $_SESSION[$key] = $value;
    }

    protected function apiResponse($data , $status = 1 , $message = '' ){
        $response = array();
        $response['status']  = $status;
        $response['message'] = $message;
        $response['data']    = empty($data) ? (object)$data : $data ;
        return response()->json($response);
    }

}
