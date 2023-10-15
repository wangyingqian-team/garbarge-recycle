<?php

use Illuminate\Support\Facades\Route;

Route::any('test', 'Controller@test');
Route::get('doc/{section?}', function ($any) {
    return file_get_contents(base_path("doc/{$any}.md"));
});
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/**
 * 前台公众号接口
 */
Route::group(['prefix' => 'official', 'namespace' => 'Official'], function () {
    require_once 'apis/official.php';
});

/**
 * 总后台接口
 */
Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function () {
    require_once 'apis/admin.php';
});

/**
 * 公共接口
 */
Route::group(['prefix' => 'common', 'namespace' => 'Common'], function () {
    require_once 'apis/common.php';
});
