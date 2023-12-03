<?php

use Illuminate\Support\Facades\Route;

// 首页
Route::get('index', 'IndexController@index');


/**=========================
 * 用户相关
 * ========================
 */

// 用户注册
Route::post('user/register', 'UserController@register');

// 用户详情
Route::get('user/detail', 'UserController@getUserDetail');

// 用户修改
Route::post('user/update', 'UserController@updateInfo');

//用户签到
Route::post('user/sign', 'UserController@sign');

//小区列表
Route::get('village/list', 'UserController@getVillageList');

//添加地址
Route::post('address/add', 'UserController@createAddress');

//修改地址
Route::post('address/edit', 'UserController@updateAddress');

//删除地址
Route::post('address/delete', 'UserController@deleteAddress');

//地址列表
Route::get('address/list', 'UserController@getAddressList');

//地址详情
Route::get('address/detail', 'UserController@getAddressDetail');

/**
 * ===============================
 * 优惠券相关
 * =================================
 */
//获取优惠券
Route::post('coupon/get', 'UserController@getCoupon');

//优惠券详情
Route::get('coupon/detail', 'UserController@getCouponDetail');

//优惠券列表
Route::get('coupon/list', 'UserController@getCouponList');


/**=========================
 * 活动相关
 * ========================
 */
//绿豆列表
Route::get('bean/list', 'UserController@getBeanList');

//下级推广列表
Route::get('invite/sub_list', 'UserController@getSubInvitationList');

//抽奖
Route::post('chou', 'UserController@chou');

/**
 * ===============================
 * 积分商城相关
 * =================================
 */
//积分商品列表
Route::get('jifen/item/list', 'JifenController@getItemList');
//积分商品详情
Route::get('jifen/item/detail', 'JifenController@getItemDetail');
//兑换
Route::post('jifen/order/create', 'JifenController@createOrder');
//积分订单列表
Route::get('jifen/order/list', 'JifenController@getOrderList');
//积分订单详情
Route::get('jifen/order/detail', 'JifenController@getOrderDetail');







/*
|--------------------------------------------------------------------------
| 回收相关
|--------------------------------------------------------------------------
*/
// 获取垃圾分类与价格
Route::get('recycle/garbageType/price', 'GarbageRecycleController@getGarbageTypePriceList');

// 选择可回收的时间段列表
Route::get('recycle/timePeriod/list', 'GarbageRecycleController@getRecycleTimePeriodList');

// 创建回收订单
Route::post('recycle/order/create', 'GarbageRecycleController@createGarbageRecycleOrder');

// 回收员订单接单
Route::put('recycle/order/receive', 'GarbageRecycleController@receiveRecycleOrder');

// 回收员上门
Route::put('recycle/order/start', 'GarbageRecycleController@startRecycleOrder');

// 回收员设置订单分类明细
Route::put('recycle/order/setDetails', 'GarbageRecycleController@setRecycleOrderDetails');

// 回收员完成回收订单（结算！！）
Route::put('recycle/order/finish', 'GarbageRecycleController@finishRecycleOrder');

// 用户回收历史订单列表（只显示已完成的订单）
Route::get('recycle/order/list', 'GarbageRecycleController@getUserRecycleOrderList');

// 用户回收订单详情
Route::get('recycle/order/info', 'GarbageRecycleController@getUserRecycleOrderInfo');

// 用户取消回收预约（预约取消）
Route::get('recycle/order/cancelByUser', 'GarbageRecycleController@cancelRecycleOrderByUser');

// 回收员取消回收订单
Route::get('recycle/order/cancelByRecycler', 'GarbageRecycleController@cancelRecycleOrderByRecycler');

// 用户爽约取消回收订单
Route::get('recycle/order/cancelByBp', 'GarbageRecycleController@cancelRecycleOrderByBp');

// 回收员回收订单列表
Route::get('recycle/recycler/order/list', 'GarbageRecycleController@getRecyclerRecycleOrderList');

// 回收员回收订单详情
Route::get('recycle/recycler/order/info', 'GarbageRecycleController@getRecyclerRecycleOrderInfo');
