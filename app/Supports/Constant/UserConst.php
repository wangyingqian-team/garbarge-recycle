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
        //抽奖次数
        'chou_jiang' => [
            1,
            1,
            2,
            2,
            3,
            3,
            3,
            4,
            4,
            5
        ],
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

}
