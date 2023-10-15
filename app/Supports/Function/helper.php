<?php

/**
 * 获取服务|服务管理者
 */


use App\Services\Common\AliOssService;
use App\Supports\Util\ServiceManager;
use Illuminate\Support\Carbon;

if (!function_exists('get_service')) {
    function get_service($abstract)
    {
        return app($abstract);
    }
}


if (!function_exists('decorate_stack')) {
    function decorate_stack($decorates)
    {
        return app(ServiceManager::class)->decorateStack($decorates);
    }
}

if (!function_exists('decorate')) {
    function decorate($data)
    {
        return app(ServiceManager::class)->decorate($data);
    }
}

if (!function_exists('dto')) {
    function dto($dao)
    {
        return app(ServiceManager::class)->getDto($dao);
    }
}

if (!function_exists('query')) {
    function query($model, $args, $sole = false)
    {
        $result = $model::query()->macroQuery(
            $args['where'] ?? [],
            $args['select'] ?? ['*'],
            $args['order_by'] ?? [],
            $args['page'] ?? 0,
            $args['limit'] ?? \App\Supports\Macro\Builder::PER_LIMIT,
            $args['with_page'] ?? true
        );

        return $sole ? (empty($result) ? [] : head($result)) : $result;
    }
}


if (!function_exists("distance")) {
    function distance($lat1, $lng1, $lat2, $lng2)
    {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1);// deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;

        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137;

        return sprintf("%.2f", abs($s));
    }
}

/**
 * 随机字符串
 */
if (!function_exists('nonce_str')) {
    function nonce_str()
    {
        [$usec, $sec] = explode(" ", microtime());
        $now_date = getdate($sec);

        $now_usec = floor($usec * 1000);
        $tody_time = $now_date['hours'] * 3600 + $now_date['minutes'] * 60 + $now_date['seconds']; // 今天过去了多少秒
        $today_time_str = substr(strval($tody_time + 100000), 1, 5); // 5位长度

        $nonce_str = date('ymd', $sec) . $today_time_str . $now_usec;
        return $nonce_str;
    }
}

/**
 * 随机字符串
 */
if (!function_exists('invite_code')) {
    function invite_code()
    {
        $a = range('A', 'Z');

        return $a[mt_rand(0, 25)] . $a[mt_rand(0, 25)] . mt_rand(1000, 9999);
    }
}

if (!function_exists('privilege_format')) {
    function privilege_format($rule)
    {
        $b = [];
        $p = \config('privilege');
        foreach ($p as $v) {
            $n = array_reverse($v['group']);
            foreach ($n as $k => $g) {
                if (isset($rule[$v['name']])) {
                    if ($rule[$v['name']] >= $g) {
                        $b[$v['name']][$k] = true;
                        $rule[$v['name']] -= $g;
                    } else {
                        $b[$v['name']][$k] = false;
                    }
                } else {
                    $b[$v['name']][$k] = false;
                }
            }
            $b[$v['name']] = array_reverse($b[$v['name']]);
        }

        return $b;
    }
}

if (!function_exists('generate_order_no')) {
    /**
     * 生成订单号.
     *
     * @param int $userId
     * @param string $prefix
     * @return string order no
     */
    function generate_order_no($userId, $prefix)
    {
        $rand = rand(100, 999);

        $userId = str_pad($userId, 6, '0', STR_PAD_LEFT);

        $timeStamp = Carbon::now()->format("YmdHis");

        return $prefix . $timeStamp . $userId . $rand;
    }
}


/**
 * 获取文件拓展名
 */
if (!function_exists('get_ext')) {
    function get_ext($filename)
    {
        $arr = explode('.', basename($filename));

        return end($arr);
    }
}

/**
 * 获取文件惟一名
 */
if (!function_exists('get_unique_name')) {
    function get_unique_name()
    {
        return date('YmdHis', time()) . mt_rand(1000, 9999);
    }
}


/**
 * 获取存入数据库的图片链接
 */
if (!function_exists('get_oss_url')) {
    function get_oss_url($url)
    {
        [, $u] = explode('com/', $url);
        return $u;
    }
}


/**
 * 获取存入数据库的图片链接
 */
if (!function_exists('batch_get_oss_url')) {
    function batch_get_oss_url($array, $column)
    {
        $columns = \Illuminate\Support\Arr::wrap($column);
        foreach ($array as &$value) {
            foreach ($columns as $column) {
                [, $value[$column]] = explode('com/', $value[$column]);
            }
        }

        return $array;
    }
}

/**
 * 设置返回给前端图片链接
 */
if (!function_exists('set_oss_url')) {
    function set_oss_url($url)
    {
        return app(AliOssService::class)->getUrl($url);
    }
}

/**
 * 设置返回给前端图片链接
 */
if (!function_exists('batch_set_oss_url')) {
    function batch_set_oss_url($array, $column)
    {
        $columns = \Illuminate\Support\Arr::wrap($column);

        $rows = isset($array['items']) ? $array['items'] : $array;
        foreach ($rows as &$value) {
            foreach ($columns as $column) {
                $value[$column] = app(AliOssService::class)->getUrl($value[$column]);
            }
        }
        isset($array['items']) ? $array['items'] = $rows : $array = $rows;

        return $array;
    }
}

/**
 * 路由配置设置
 */
if (!function_exists('route_config')) {
    function route_config($name)
    {
        return config('privilege.' . $name);
    }
}

/**
 * 过滤数组中null
 */
if (!function_exists('array_null')) {
    function array_null($array)
    {
        return array_filter(
            $array,
            function ($v) {
                return !is_null($v);
            }
        );
    }
}

/**
 * 判断时间是否重叠.
 */
if (!function_exists('is_time_overlap')) {
    function is_time_overlap($startTime1, $endTime1, $startTime2, $endTime2)
    {
        return strtotime($endTime1) > strtotime($startTime2) && strtotime($startTime1) < strtotime($endTime2);
    }
}

