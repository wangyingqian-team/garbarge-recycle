<?php

use App\Models\PermissionModel;
use App\Models\RoleModel;
use Illuminate\Database\Seeder;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // create permissions
        PermissionModel::create(['id' => '1', 'pid' => '0', 'lvl' => '0', 'key' => 'System Management', 'name' => '系统管理', 'type' => '1']);
        PermissionModel::create(['id' => '2', 'pid' => '1', 'lvl' => '1', 'key' => 'Mandatory field management', 'name' => '必填项管理', 'type' => '1']);

        RoleModel::create(['id' => '1', 'name' => '超级管理员', 'creat_account_uid' => '', 'description' => '超级管理员拥有所有权限', 'type' => '1']);
    }
}
