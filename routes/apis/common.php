<?php
use Illuminate\Support\Facades\Route;
//图片上传
Route::post('upload', 'ImageController@upload');

//公告
Route::get('announcement', 'NotifyController@announcement');

// webhook
Route::get('webhook', 'WebHookController@pushEvent');

//图形验证码
Route::post('captcha', 'ImageController@captcha');

//短信验证码
Route::post('sms', 'ImageController@sms');
