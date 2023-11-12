<?php

namespace App\Supports\Constant;

/**
 * Class GarbageRecycleConst.
 *
 * @package App\Supports\Constant
 */
class GarbageRecycleConst
{
    /*
    |----------------------------------------
    | 回收订单常量配置KEY
    |----------------------------------------
    */
    /**
     * 回收垃圾时间段配置
     */
    const GARBAGE_RECYCLE_APPOINT_PERIOD = 'recycle_appoint_period';

    /**
     * 回收垃圾每个时间段最大预约单数
     */
    const GARBAGE_RECYCLE_MAX_ORDERS_PER_PERIOD = 'recycle_appoint_period';

    /*
    |----------------------------------------
    | 回收订单状态
    |----------------------------------------
    */
    /**
     * 回收垃圾订单状态：已预约
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_RESERVED = 10;

    /**
     * 回收垃圾订单状态：已接单
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED = 20;

    /**
     * 回收垃圾订单状态：回收中
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_RECYCLING = 30;

    /**
     * 回收垃圾订单状态：已完成
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_FINISHED = 40;

    /**
     * 回收垃圾订单状态：已取消（用户主动取消）
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_USER_CANCELED = 51;

    /**
     * 回收垃圾订单状态：已取消（回收员主动取消，一般是提前感知来不及上门了）
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_RECYCLER_CANCELED = 52;

    /**
     * 回收垃圾订单状态：已取消（用户爽约取消）
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_BREAK_PROMISE_CANCELED = 53;

    /**
     * 回收垃圾订单状态：已取消（回收员上门超时取消，系统定时任务触发该状态）
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_TIMEOUT_CANCELED = 54;

    /**
     * 回收垃圾订单状态映射
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_MAP = [
        self::GARBAGE_RECYCLE_ORDER_STATUS_RESERVED => '已预约',
        self::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED => '已接单',
        self::GARBAGE_RECYCLE_ORDER_STATUS_RECYCLING => '回收中',
        self::GARBAGE_RECYCLE_ORDER_STATUS_FINISHED => '已完成',
        self::GARBAGE_RECYCLE_ORDER_STATUS_USER_CANCELED => '已取消（用户主动取消）',
        self::GARBAGE_RECYCLE_ORDER_STATUS_RECYCLER_CANCELED => '已取消（回收员主动取消）',
        self::GARBAGE_RECYCLE_ORDER_STATUS_BREAK_PROMISE_CANCELED => '已取消（用户爽约取消）',
        self::GARBAGE_RECYCLE_ORDER_STATUS_TIMEOUT_CANCELED => '已取消（回收员上门超时取消）'
    ];

    /**
     * 进行中（未完成）的订单状态枚举
     */
    const GARBAGE_RECYCLE_ORDER_STATUS_ONGOING = [
        self::GARBAGE_RECYCLE_ORDER_STATUS_RESERVED,
        self::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED,
        self::GARBAGE_RECYCLE_ORDER_STATUS_RECYCLING
    ];

    /**
     * 判断订单是否为进行中订单
     * @param $orderStatus int 订单状态
     * @return bool 判断结果
     */
    public static function isOrderOnGoing($orderStatus) {
        return in_array($orderStatus, self::GARBAGE_RECYCLE_ORDER_STATUS_ONGOING);
    }
}
