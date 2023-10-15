<?php

namespace App\Supports\Constant;

class JifenConst
{

    /**========================
     * 订单公共常量、
     * ========================
     */

    /**=============================
     * 积分订单常量
     * ==========================
     */

    const JI_FEN_ORDER_PREFIX = 'J'; //积分订单前缀

    const JI_FEN_ORDER_WAIT_STATUS = 1; //待配送

    const JI_FEN_ORDER_CONFIRM_STATUS = 2; //配送中

    const JI_FEN_ORDER_FINISH_STATUS = 3; //完成

    const JI_FEN_ORDER_USER_CANCEL_STATUS = 8; //用户取消

    const JI_FEN_ORDER_SYSTEM_CANCEL_STATUS = 9; //系统取消

    const JI_FEN_ORDER_STATUS_MAP = [  //订单状态映射
        self::JI_FEN_ORDER_WAIT_STATUS => '待配送',
        self::JI_FEN_ORDER_CONFIRM_STATUS => '配送中',
        self::JI_FEN_ORDER_FINISH_STATUS => '已完成',
        self::JI_FEN_ORDER_USER_CANCEL_STATUS => '用户取消',
        self::JI_FEN_ORDER_SYSTEM_CANCEL_STATUS => '系统取消'
    ];

    const JI_FEN_DELIVERY_ONE = 1; //送货上门

    const JI_FEN_DELIVERY_TWO = 2; //商家配送

    const JI_FEN_DELIVERY_MAP = [
        self::JI_FEN_DELIVERY_ONE => '送货上门',
        self::JI_FEN_DELIVERY_TWO => '商家配送'
    ];

}
