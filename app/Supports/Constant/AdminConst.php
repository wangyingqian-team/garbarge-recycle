<?php
namespace App\Supports\Constant;

class AdminConst {

    const SUPPER_ADMIN_ROLE_ID = 1;//超级管理员角色id

    const ADMIN_SALT = 'garbage:'; //密码加盐

    const TOKEN_TIME = 86400;

    const LOGIN_ABLE = 1; //启用

    const LOGIN_DISABLE = 2; //禁用
}
