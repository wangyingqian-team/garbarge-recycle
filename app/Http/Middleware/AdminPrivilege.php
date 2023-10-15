<?php

namespace App\Http\Middleware;

use App\Exceptions\RestfulException;
use App\Services\Admin\AdminService;
use App\Supports\Constant\AdminConst;
use Closure;

class AdminPrivilege
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
        //获取请求头中的role_id
        $roleId = $request->header('roleId');;
        if (empty($roleId)) {
            throw new RestfulException('未分配角色!', 2000);
        }

        //非超级管理员需要校验权限
        if ($roleId != AdminConst::SUPPER_ADMIN_ROLE_ID) {
            $r = app(AdminService::class)->getPrivilege($roleId);
            if (empty($r)) {
                throw new RestfulException('未分配角色权限!', 2000);
            }
            $privilege = $request->route()->getName();
            if (!in_array($privilege, $r)) {
                throw new RestfulException('权限不足!', 2000);
            }
        }


        return $next($request);
    }
}
