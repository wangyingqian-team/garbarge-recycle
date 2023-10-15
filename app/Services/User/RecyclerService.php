<?php

namespace App\Services\User;

use App\Dto\RecyclerDto;
use App\Dto\UserDto;
use App\Supports\Constant\RecyclerConst;
use App\Supports\Constant\UserConst;
use Illuminate\Support\Facades\DB;

class RecyclerService
{
    /**
     * 添加回收员
     *
     * @param $data
     * @return bool
     */
    public function createRecycler($data)
    {
        DB::transaction(function ($data) {
            $data['front_image'] = get_oss_url($data['front_image']);
            $data['back_image'] = get_oss_url($data['back_image']);
            app(RecyclerDto::class)->create($data);
            app(UserDto::class)->update($data['user_id'], ['is_recycler' => UserConst::IS_RECYCLER]);
        });
        return true;
    }

    /**
     * 更新回收员
     *
     * @param $id
     * @param $data
     * @return bool
     */
    public function updateRecycler($id, $data) {
        return app(RecyclerDto::class)->update($id, $data);
    }

    /**
     * 删除回收员
     *
     * @param $id
     * @param $userId
     * @return bool
     * @throws \Throwable
     */
    public function deleteRecycler($id, $userId) {
        DB::transaction(function () use($id, $userId) {
            app(RecyclerDto::class)->update($id, ['status'=>RecyclerConst::IS_DELETE]);
            app(UserDto::class)->update($userId, ['is_recycler' => UserConst::IS_NOT_RECYCLER]);
        });
        return true;
    }

    /**
     * 回收员列表
     *
     * @param $where
     * @param array $select
     * @param array $orderBy
     * @param int $page
     * @param int $limit
     * @param bool $withPage
     * @return mixed
     */
    public function getRecyclerList($where, $select = ['*'], $orderBy = [], $page = 0, $limit= 0, $withPage = true) {
        $data = app(RecyclerDto::class)->getList($where, $select, $orderBy, $page, $limit, $withPage);
        return $data;
    }

    /**
     * 回收员详情
     *
     * @param $recyclerId
     * @param array $select
     * @return mixed
     */
    public function getRecyclerDetail($recyclerId,$select = ['*']) {
        $data = app(RecyclerDto::class)->getDetail(['id'=>$recyclerId], $select);
        return batch_set_oss_url($data, ['front_image', 'back_image']);
    }

}
