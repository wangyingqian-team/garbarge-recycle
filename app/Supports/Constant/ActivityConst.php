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
}
