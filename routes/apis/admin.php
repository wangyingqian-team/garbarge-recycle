<?php

use Illuminate\Support\Facades\Route;

// 管理员账号注册
Route::post('account/register', 'AdminController@register');

// 管理员账号登录
Route::post('account/login', 'AdminController@login');

Route::group(
    [
        'middleware' => [
            \App\Http\Middleware\AdminAccountAuthenticate::class,
            \App\Http\Middleware\AdminPrivilege::class
        ]
    ],
    function () {
        //首页
        Route::get('index', 'AdminController@index')
            ->name(route_config('index'));

        //设置配置
        Route::post('config/set', 'ConfigController@setConfig')
            ->name(route_config('config.set'));

        //获取配置
        Route::get('config/get', 'ConfigController@getConfig')
            ->name(route_config('config.get'));

        /**=========================
         * 用户相关
         * ========================
         */
        Route::get('user/address/list', 'UserController@getUserAddress')
            ->name(route_config('address.list'));

        /**=========================
         * 小区相关
         * ========================
         */
        //添加小区
        Route::post('village/add', 'VillageController@createVillage')
            ->name('village.add');

        //修改小区
        Route::post('village/edit', 'VillageController@updateVillage')
            ->name('village.edit');

        //删除小区
        Route::post('village/delete', 'VillageController@deleteVillage')
            ->name('village.delete');

        //小区列表
        Route::get('village/list', 'VillageController@getVillageList')
            ->name('village.list');

        //小区详情
        Route::get('village/detail', 'VillageController@getVillageDetail')
            ->name('village.detail');

        /**
         * ===============================================
         * 垃圾站点
         */
        Route::post('garbage/site/create', 'GarbageSiteStationController@createGarbageSite')
            ->name('garbageSite.create');

        Route::post('garbage/site/edit', 'GarbageSiteStationController@updateGarbageSite')
            ->name('garbageSite.edit');

        Route::post('garbage/site/delete', 'GarbageSiteStationController@deleteGarbageSite')
            ->name('garbageSite.delete');

        Route::get('garbage/site/list', 'GarbageSiteStationController@getGarbageSiteList')
            ->name('garbageSite.list');

        Route::get('garbage/site/detail', 'GarbageSiteStationController@getGarbageSiteDetail')
            ->name('garbageSite.detail');
        /*
        |--------------------------------------------------------------------------
        | 垃圾大类与垃圾种类
        |--------------------------------------------------------------------------
        */
        Route::get('garbage/category/list', 'GarbageCategoryController@getGarbageCategoryList')
            ->name(route_config('garbageCategory.list'));

        Route::get('garbage/category/info', 'GarbageCategoryController@getGarbageCategoryInfo')
            ->name(route_config('garbageCategory.info'));

        Route::post('garbage/category/add', 'GarbageCategoryController@addGarbageCategory')
            ->name(route_config('garbageCategory.add'));

        Route::post('garbage/category/edit', 'GarbageCategoryController@updateGarbageCategory')
            ->name(route_config('garbageCategory.edit'));

        Route::get('garbage/category/delete', 'GarbageCategoryController@deleteGarbageCategory')
            ->name(route_config('garbageCategory.delete'));

        Route::get('garbage/type/list', 'GarbageTypeController@getGarbageTypeList')
            ->name(route_config('garbageType.list'));

        Route::get('garbage/type/info', 'GarbageTypeController@getGarbageTypeInfo')
            ->name(route_config('garbageType.info'));

        Route::post('garbage/type/add', 'GarbageTypeController@addGarbageType')
            ->name(route_config('garbageType.add'));

        Route::post('garbage/type/edit', 'GarbageTypeController@updateGarbageType')
            ->name(route_config('garbageType.edit'));

        Route::get('garbage/type/delete', 'GarbageTypeController@deleteGarbageType')
            ->name(route_config('garbageType.delete'));

        /*
        |--------------------------------------------------------------------------
        | 垃圾售卖站
        |--------------------------------------------------------------------------
        */
        Route::get('garbage/sellStation/list', 'GarbageSellStationController@getGarbageSellStationList')
            ->name(route_config('garbageSellStation.list'));

        Route::get('garbage/sellStation/info', 'GarbageSellStationController@getGarbageSellStationInfo')
            ->name(route_config('garbageSellStation.info'));

        Route::post('garbage/sellStation/add', 'GarbageSellStationController@addGarbageSellStation')
            ->name(route_config('garbageSellStation.add'));

        Route::post('garbage/sellStation/update', 'GarbageSellStationController@updateGarbageSellStation')
            ->name(route_config('garbageSellStation.edit'));

        Route::get('garbage/sellStation/delete', 'GarbageSellStationController@deleteGarbageSellStation')
            ->name(route_config('garbageSellStation.delete'));

        /*
         * =======================================
         *
         * 积分商城相关
         *
         * =========================================
         */
        Route::post('jifen/item/create', 'JifenController@createItem')
            ->name('jifenItem.create');

        Route::post('jifen/item/edit', 'JifenController@updateItem')
            ->name('jifenItem.edit');

        Route::post('jifen/item/delete', 'JifenController@deleteItem')
            ->name('jifenItem.delete');

        Route::get('jifen/item/list', 'JifenController@getItemList')
            ->name('jifenItem.list');

        Route::get('jifen/order/list', 'JifenController@getOrderList')
            ->name('jifenOrder.list');

        Route::get('jifen/order/detail', 'JifenController@getOrderDetail')
            ->name('jifenOrder.detail');
    }
);
