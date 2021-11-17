<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Payever\Santander\Controllers'], function () {
    Route::get('santander-payment/test', 'PaymentController@test');
    Route::get('santander-payment/orders/{orderId}/notify', 'PaymentController@notify');
});
