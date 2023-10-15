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

//用户签到
Route::post('user/sign', 'UserController@sign');

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


/**=========================
 * 优惠券相关
 * ========================
 */
//代扔券列表
Route::get('coupon/throw/list', 'CouponController@getThrowCouponList');


/**=========================
 * 活动相关
 * ========================
 */
//领取活动奖励
Route::post('activity/receive', 'UserController@receiveActivityReward');

//邀请有礼活动列表
Route::get('activity/invite/list', 'UserController@getInviteActivityList');

//新人福利活动列表
Route::get('activity/newer/list', 'UserController@getNewerActivityList');

/**=========================
 * 小区相关
 * ========================
 */
//获取附近小区
Route::get('village/list', 'UserController@getNearVillageList');


/*
|--------------------------------------------------------------------------
| 回收相关
|--------------------------------------------------------------------------
*/
// 获取垃圾分类与价格
Route::get('recycle/garbageType/price', 'GarbageRecycleController@getGarbageTypePriceList');

// 获取常用垃圾分类
Route::get('recycle/garbage/popular/types', 'GarbageRecycleController@getPopularGarbageTypes');

// 选择可回收的时间段列表
Route::get('recycle/timePeriod/list', 'GarbageRecycleController@getRecycleTimePeriodList');

// 创建回收订单
Route::post('recycle/order/create', 'GarbageRecycleController@createGarbageRecycleOrder');

// 用户确认回收订单（确认为已完成）
Route::get('recycle/order/confirm', 'GarbageRecycleController@confirmRecycleOrderByUser');

// 用户取消回收预约（预约取消）
Route::get('recycle/order/cancelByReserve', 'GarbageRecycleController@cancelRecycleOrderByUserReserve');

// 用户取消回收订单（确认取消）
Route::get('recycle/order/cancelByConfirm', 'GarbageRecycleController@cancelRecycleOrderByUserConfirm');

// 用户评价回收订单
Route::post('recycle/order/rate/add', 'GarbageRecycleController@rateGarbageRecycleOrder');

// 用户我的回收评价列表
Route::get('recycle/order/rate/list', 'GarbageRecycleController@getUserRecycleRateList');

// 用户回收订单列表
Route::get('recycle/order/list', 'GarbageRecycleController@getUserRecycleOrderList');

// 用户回收订单详情
Route::get('recycle/order/info', 'GarbageRecycleController@getUserRecycleOrderInfo');

// 回收员确认回收订单（确认为待完成）
Route::post('recycle/recycler/order/confirm', 'GarbageRecycleController@confirmRecycleOrderByRecycler');

// 回收员取消回收订单
Route::get('recycle/recycler/order/cancel', 'GarbageRecycleController@cancelRecycleOrderByRecycler');

// 回收员回收订单列表
Route::get('recycle/recycler/order/list', 'GarbageRecycleController@getRecyclerRecycleOrderList');

// 回收员回收订单详情
Route::get('recycle/recycler/order/info', 'GarbageRecycleController@getRecyclerRecycleOrderInfo');
