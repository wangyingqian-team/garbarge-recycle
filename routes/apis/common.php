<?php
use Illuminate\Support\Facades\Route;
//图片上传
Route::post('upload', 'ImageController@upload');

//公告
Route::get('announcement', 'NotifyController@announcement');
