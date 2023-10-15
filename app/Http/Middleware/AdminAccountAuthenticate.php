<?php

namespace App\Http\Middleware;

use App\Exceptions\RestfulException;
use App\Supports\Constant\AdminConst;
use App\Supports\Constant\RedisKeyConst;
use Closure;
use Illuminate\Support\Facades\Redis;

class AdminAccountAuthenticate
{
    /**
     * 平台管理员账号鉴权
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param bool $optional
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     */
    public function handle($request, Closure $next)
    {
         //获取请求头中的token
        $token = $request->header('token');
        if (empty($token)){
            throw new RestfulException('管理员账号未登录!', 1000);
        }
        $adminId = $request->header('adminId');
        $redis = Redis::connection('admin');
        $t = $redis->get(RedisKeyConst::ADMIN_TOKEN.':'.$adminId);
        if (empty($t)){
            throw new RestfulException('管理员账号登录过期!',1000);
        }
        if ($token != $t) {
            throw new RestfulException('管理员账号登录错误!',1000);
        }

        return $next($request);
    }
}
