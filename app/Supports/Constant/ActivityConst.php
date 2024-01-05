<?php

namespace App\Supports\Constant;

class ActivityConst
{

    const ACTIVITY_INVITATION_DEFAULT_LEVEL = 0; //绑定关系等级,最高7级， 会影响绿豆返还倍数.

    //返还绿豆倍数。 绿豆 = 被邀请人卖出的垃圾金额 * 倍数
    const ACTIVITY_BEAN_MULTI = [
        0,
        0.02,
        0.03,
        0.04,
        0.05,
        0.06,
        0.07,
        0.08
    ];

    //关系升级所需人数
    const ACTIVITY_INVITATION_LEVEL = [
        0,
        3,
        5,
        10,
        18,
        30,
        50
    ];

    // 绿豆提现比例（一个绿豆一分钱）
    const BEAN_WITHDRAW_RATIO = 0.01;

    // 5元代金券 优惠券ID
    const COUPON_ID_5_YUAN = 12;

    // 积分兑换比例（支付1元兑换100积分）
    const JIFEN_EXCHANGE_AMOUNT = 100;

    // 首单福利优惠券：5元代金券
    const FIRST_ORDER_COUPON_ID = 13;
    const FIRST_ORDER_COUPON_NAME = '5元代金券';

    // 多卖多送优惠券规则
    const SELL_GIVE_COUPON_RULES = [
        // 完成2次订单可领取2元代金券
        array("order_quantity" => 2, "coupon_id" => 12, "coupon_name" => '2元代金券'),
        // 完成5次订单可领取5元代金券
        array("order_quantity" => 5, "coupon_id" => 13, "coupon_name" => '5元代金券'),
        // 完成10次订单可领取10元代金券
        array("order_quantity" => 10, "coupon_id" => 14, "coupon_name" => '10元代金券'),
        // 完成15次订单可领取15元代金券
        array("order_quantity" => 15, "coupon_id" => 15, "coupon_name" => '15元代金券'),
        // 完成20次订单可领取20元代金券
        array("order_quantity" => 20, "coupon_id" => 16, "coupon_name" => '20元代金券')
    ];

    // 用户补偿优惠券: 5元代金券
    const ORDER_COMPENSATE_COUPON_ID = 13;
}
