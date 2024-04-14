<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/404', function () {
    return view('web.components.404');
})->name('404');
Route::get('/notworking', function () {
    return view('web.components.notworking');
})->name('notworking');

Route::get('/result', function () {
    return view('web.aia.result');
});


Route::domain(env('DOMAIN_UNILEVER_TET', 'http://game-vongquaymayman.local'))->group(function () {
    Route::any('/',['uses'=>'Web\UnileverTetController@index'])->name('utet.index');
    Route::any('/unilever/otp/{customer_id}/{campaign_id}',['uses'=>'Web\UnileverTetController@verifyOtp'])->name('utet.otp');
    Route::any('/unilever/game/{customer_id}/{campaign_id}',['uses'=>'Web\UnileverTetController@chooseGame'])->name('utet.game.choose');
    Route::post('/unilever/resend', ['uses'=>'Web\UnileverTetController@resendOtp'])->name('utet.resend.otp');
    Route::any('/game/intro/{customer_id}/{campaign_id}/{uuid}', ['uses'=>'Web\UnileverTetController@introGame'])->name('utet.game.intro');
    Route::post('/game/check', ['uses'=>'Web\UnileverTetController@checkGame'])->name('utet.game.check');
    Route::any('/game/play/{type}/{customer_id}/{campaign_id}/{uuid}', ['uses'=>'Web\UnileverTetController@playGame'])->name('utet.game.play');
    Route::post('/unilever/record', ['uses'=>'Web\UnileverTetController@recordPlayGame'])->name('utet.game.record');
    Route::any('/game/result/{customer_id}/{campaign_id}/{uuid}', ['uses'=>'Web\UnileverTetController@resultGame'])->name('utet.game.result');
    Route::any('/game/spin/{customer_id}/{campaign_id}/{uuid}',['uses'=>'Web\UnileverTetController@getSpin'])->name('utet.game.spin');
    Route::post('/process/spin', ['uses' => 'Web\UnileverTetController@processSpin'])->name('utet.process.spin');
    Route::any('/game/spin/result/{customer_id}/{campaign_id}/{order_id}/{uuid}',['uses'=>'Web\UnileverTetController@resultSpin'])->name('utet.game3.spin.result');
    Route::any('/unilever/notworking', function(){
        return view('web.unilever_tet.notworking');
    })->name('utet.notworking');
});
