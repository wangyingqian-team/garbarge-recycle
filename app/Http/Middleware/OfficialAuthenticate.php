<?php
namespace App\Http\Middleware;
use App\Exceptions\RestfulException;

/**
 * 用户信息校验中间件
 */
class OfficialAuthenticate
{
    /**
     * 公众号用户信息校验
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     *
     */
    public function handle($request, \Closure $next)
    {
        $userToken = $request->header('token');

        if (empty($userToken) || $userToken != md5('user_token')) {
            throw new RestfulException('用户未授权登录，请先授权！');
        }

        return $next($request);
    }
}