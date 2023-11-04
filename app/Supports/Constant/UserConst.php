<?php

namespace App\Supports\Constant;

class UserConst
{
    const IS_DEFAULT_ADDRESS = 1; //默认地址

    const IS_NOT_DEFAULT_ADDRESS = 2; //非默认地址

    const IS_RECYCLER = 1; //是回收员

    const IS_NOT_RECYCLER = 2; //非回收员

    //会员最高等级
    const LEVEL_MAX = 9;

    //会员等级所需经验
    const LEVEL_EXP = [
        0,
        3000,
        8000,
        16000,
        28000,
        42000,
        60000,
        82000,
        112000,
        150000
    ];

    //会员权益
    const LEVEL_EQUITY = [

        //额外积分
        'ji_fen_extra' => [
            0,
            0.1,
            0.16,
            0.2,
            0.24,
            0.28,
            0.31,
            0.34,
            0.37,
            0.4
        ],
        //特定垃圾会员价格
        'price_extra' => [
            0,
            0.05,
            0.08,
            0.11,
            0.13,
            0.15,
            0.18,
            0.2,
            0.22,
            0.25
        ],
    ];

    //默认信用值
    const DEFAULT_CREDIT = 100;

    //低于50信用值将无法预约
    const MIN_CREDIT = 50;

    //信用值变化情况
    const CREDIT_CHANGE = [
        'increase' => [
            'sign' => 2, //签到
            'garbage_sell_count' => 5, //完成一次垃圾售卖
        ],
        'decrease' => [
            'short_weight' => 7, //没有达到起收重量
            'break_promise' => 10, // 爽约
            'deception' => 25, // 售卖过程中出现欺骗行为，比如纸壳加湿，参杂等行为
        ],
        'month_settle' => [
            [
                'amount' => 100,
                'number' => 30,
            ],
            [
                'amount' => 50,
                'number' => 20,
            ],
            [
                'amount' => 20,//当月完成20元垃圾售卖
                'number' => 10, //奖励10信用
            ],

        ]
    ];

    /*
    |----------------------------------------
    | 小区相关常量配置
    |----------------------------------------
    */
    /**
     * 小区是否开放：是
     */
    const VILLAGE_STATUS_ACTIVE = 1;

    /**
     * 小区是否开放：否
     */
    const VILLAGE_STATUS_INACTIVE = 2;
}
