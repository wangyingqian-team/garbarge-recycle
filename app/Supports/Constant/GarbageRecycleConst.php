<?php

namespace App\Supports\Constant;

class GarbageRecycleConst
{
    /*
    |----------------------------------------
    | 废品垃圾种类
    |----------------------------------------
    */
    /**
     * 废品垃圾种类：常用种类
     */
    const GARBAGE_RECYCLE_TYPE_POPULAR = 1;

    /**
     * 废品垃圾种类：非常用种类
     */
    const GARBAGE_RECYCLE_TYPE_UNPOPULAR = 2;

    /**
     * 废品垃圾种类
     */
    const GARBAGE_RECYCLE_TYPES = [
        self::GARBAGE_RECYCLE_TYPE_POPULAR,
        self::GARBAGE_RECYCLE_TYPE_UNPOPULAR
    ];

    /*
    |----------------------------------------
    | 代仍垃圾订单状态
    |----------------------------------------
    */
    /**
     * 回收垃圾订单状态：已预约
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_RESERVED = 1;

    /**
     * 回收垃圾订单状态：已接单
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED = 2;

    /**
     * 回收垃圾订单状态：待完成
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_WAIT_FINISH = 3;

    /**
     * 回收垃圾订单状态：已完成
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_FINISHED = 4;

    /**
     * 回收垃圾订单状态：已评价
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_RATED = 5;

    /**
     * 回收垃圾订单状态：系统取消
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_SYSTEM_CANCELED = 6;

    /**
     * 回收垃圾订单状态：用户预约取消
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_USER_RESERVE_CANCELED = 7;

    /**
     * 回收垃圾订单状态：用户确认取消
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_USER_CONFIRM_CANCELED = 8;

    /**
     * 回收垃圾订单状态：回收员取消
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_RECYCLER_CANCELED = 9;

    /**
     * 回收垃圾订单状态映射
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_MAP = [
        self::GARBAGE_RECYCLE_ORDER_STATUS_RESERVED => '已预约',
        self::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED => '已接单',
        self::GARBAGE_RECYCLE_ORDER_STATUS_WAIT_FINISH => '待完成',
        self::GARBAGE_RECYCLE_ORDER_STATUS_FINISHED => '已完成',
        self::GARBAGE_RECYCLE_ORDER_STATUS_RATED => '已评价',
        self::GARBAGE_RECYCLE_ORDER_STATUS_SYSTEM_CANCELED => '系统取消',
        self::GARBAGE_RECYCLE_ORDER_STATUS_USER_RESERVE_CANCELED => '用户预约取消',
        self::GARBAGE_RECYCLE_ORDER_STATUS_USER_CONFIRM_CANCELED => '用户确认取消',
        self::GARBAGE_RECYCLE_ORDER_STATUS_RECYCLER_CANCELED => '回收员取消'
    ];

    /*
    |----------------------------------------
    | 回收垃圾评价类型
    |----------------------------------------
    */
    /**
     * 回收垃圾评价类型：好评
     */
    const GARBAGE_RECYCLE_RATE_TYPE_GOOD = 1;

    /**
     * 回收垃圾评价类型：差评
     */
    const GARBAGE_RECYCLE_RATE_TYPE_BAD = 2;

    /**
     * 回收垃圾评价类型
     */
    const GARBAGE_RECYCLE_RATE_TYPES = [
        self::GARBAGE_RECYCLE_RATE_TYPE_GOOD,
        self::GARBAGE_RECYCLE_RATE_TYPE_BAD
    ];

    /**
     * 回收垃圾评价类型映射
     */
    const GARBAGE_RECYCLE_RATE_TYPE_MAP = [
        self::GARBAGE_RECYCLE_RATE_TYPE_GOOD => '好评',
        self::GARBAGE_RECYCLE_RATE_TYPE_BAD => '差评'
    ];

}