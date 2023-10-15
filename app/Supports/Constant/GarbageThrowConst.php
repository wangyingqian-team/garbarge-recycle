<?php
namespace App\Supports\Constant;

class GarbageThrowConst
{
    /*
    |----------------------------------------
    | 代仍垃圾种类
    |----------------------------------------
    */
    /**
     * 代仍垃圾种类：干垃圾
     */
    const GARBAGE_THROW_TYPE_DRY = 1;

    /**
     * 代仍垃圾种类：湿垃圾
     */
    const GARBAGE_THROW_TYPE_WET = 2;

    /**
     * 代仍垃圾种类：混合垃圾
     */
    const GARBAGE_THROW_TYPE_MIX = 3;

    /**
     * 代仍垃圾种类
     */
    const GARBAGE_THROW_TYPES = [
        self::GARBAGE_THROW_TYPE_DRY,
        self::GARBAGE_THROW_TYPE_WET,
        self::GARBAGE_THROW_TYPE_MIX
    ];

    /*
    |----------------------------------------
    | 代仍垃圾订单状态
    |----------------------------------------
    */
    /**
     * 代仍垃圾订单状态：已预约
     */
    const GARBAGE_THROW_ORDER_STATUS_RESERVED = 1;

    /**
     * 代仍垃圾订单状态：已接单
     */
    const GARBAGE_THROW_ORDER_STATUS_RECEIVED = 2;

    /**
     * 代仍垃圾订单状态：已完成
     */
    const GARBAGE_THROW_ORDER_STATUS_FINISHED = 3;

    /**
     * 代仍垃圾订单状态：已评价
     */
    const GARBAGE_THROW_ORDER_STATUS_RATED = 4;

    /**
     * 代仍垃圾订单状态：系统取消
     */
    const GARBAGE_THROW_ORDER_STATUS_SYSTEM_CANCELED = 7;

    /**
     * 代仍垃圾订单状态：用户取消
     */
    const GARBAGE_THROW_ORDER_STATUS_USER_CANCELED = 8;

    /**
     * 代仍垃圾订单状态：回收员取消
     */
    const GARBAGE_THROW_ORDER_STATUS_RECYCLER_CANCELED = 9;

    /**
     * 代仍垃圾订单状态映射
     */
    const GARBAGE_THROW_ORDER_STATUS_MAP = [
        self::GARBAGE_THROW_ORDER_STATUS_RESERVED => '已预约',
        self::GARBAGE_THROW_ORDER_STATUS_RECEIVED => '已接单',
        self::GARBAGE_THROW_ORDER_STATUS_FINISHED => '已完成',
        self::GARBAGE_THROW_ORDER_STATUS_RATED => '已评价',
        self::GARBAGE_THROW_ORDER_STATUS_SYSTEM_CANCELED => '系统取消',
        self::GARBAGE_THROW_ORDER_STATUS_USER_CANCELED => '用户取消',
        self::GARBAGE_THROW_ORDER_STATUS_RECYCLER_CANCELED => '回收员取消'
    ];

    /*
    |----------------------------------------
    | 代仍垃圾评价类型
    |----------------------------------------
    */
    /**
     * 代仍垃圾评价类型：好评
     */
    const GARBAGE_THROW_RATE_TYPE_GOOD = 1;

    /**
     * 代仍垃圾评价类型：差评
     */
    const GARBAGE_THROW_RATE_TYPE_BAD = 2;

    /**
     * 代仍垃圾评价类型
     */
    const GARBAGE_THROW_RATE_TYPES = [
        self::GARBAGE_THROW_RATE_TYPE_GOOD,
        self::GARBAGE_THROW_RATE_TYPE_BAD
    ];

    /**
     * 代仍垃圾评价类型映射
     */
    const GARBAGE_THROW_RATE_TYPE_MAP = [
        self::GARBAGE_THROW_RATE_TYPE_GOOD => '好评',
        self::GARBAGE_THROW_RATE_TYPE_BAD => '差评'
    ];

    /**
     * 每张代仍券可以扔垃圾的袋数
     */
    const GARBAGE_THROW_NUMBERS_PER_COUPON = 2;
}