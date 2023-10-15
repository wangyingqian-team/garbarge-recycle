<?php
namespace App\Supports\Constant;

/**
 * 配置表常量配置
 *
 * Class MassConst
 *
 * @package App\Supports\Constant
 */
class ConfigConst
{
    const USER_ANNOUNCEMENT = 'user_announcement'; //用户端公告

    const RECYCLE_ANNOUNCEMENT = 'recycle_announcement'; //回收员端公告

    const USER_BANNER = 'user_banner'; //用户的banner

    const RECYCLE_BANNER = 'recycle_banner'; //回收员端公告

    const THROW_GARBAGE_TIME = 'throw_garbage_time'; //代扔垃圾时间段

    const THROW_GARBAGE_NUM_PER_TIME = 'throw_garbage_num_per_time'; //每小时代扔垃圾数量

    const THROW_GARBAGE_JIFEN_NUM = 'throw_garbage_jifen_num'; //每次代扔垃圾获得的能量积分

    const RECYCLE_GARBAGE_TIME = 'recycle_garbage_time'; //回收垃圾时间段

    const RECYCLE_GARBAGE_NUM_PER_TIME = 'recycle_garbage_num_per_time'; //每小时回收垃圾数量

    const RECYCLE_GARBAGE_JIFEN_NUM = 'recycle_garbage_jifen_num'; //回收垃圾每1块钱获得的能量积分

    const COMMON_UNIT = 'common_unit'; //常用单位配置

    const NEWER_CONTINUE_DAYS = 'newer_continue_days'; //新人身份天数

    const AUTO_RATE_DAYS = 'auto_rate_days'; //自动评价天数

    const THROW_GARBAGE_TYPES = 'throw_garbage_types';// 代仍垃圾种类配置
}
