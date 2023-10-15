<?php

namespace App\Http\Controllers\Admin;


use App\Exceptions\RestfulException;
use App\Services\Admin\AdminService;
use App\Services\User\RecyclerService;
use Illuminate\Http\Request;

class RecyclerController extends BaseController
{
    public function getRecyclerList()
    {
        $where = [];
        if ($this->siteId) {
            $where['site_id'] = $this->siteId;
        }

        $rows = app(RecyclerService::class)->getRecyclerList(
            $where,
            ['*', 'userInfo.*', 'assets.*'],
            ['create_time' => 'desc'],
            $this->page,
            $this->pageSize
        );

        foreach ($rows['items'] as &$row) {
            $row['today_throw_count'] = 1;
            $row['today_recycle_count'] = 1;
        }

        return $this->success($rows);
    }

    public function getRecyclerDetail()
    {
        $id = $this->request->get('recycler_id');
        $row = app(RecyclerService::class)->getRecyclerDetail($id, ['*', 'userInfo.*', 'assets.*']);
        $row['today_throw_count'] = 1;
        $rows['today_recycle_count'] = 1;

        return $this->success($rows);
    }

    public function create()
    {
        $data = [
            'user_id' => $this->request->get('user_id'),
            'site_id' => $this->request->get('site_id'),
            'real_name' => $this->request->get('real_name'),
            'mobile' => $this->request->get('mobile'),
            'status' => $this->request->get('status'),
            'id_number' => $this->request->get('id_number'),
            'front_image' => $this->request->get('front_image'),
            'back_image' => $this->request->get('back_image')
        ];

        app(RecyclerService::class)->createRecycler($data);

        return $this->success();
    }

    public function update()
    {
        $id = $this->request->get('recycler_id');
        $data = [
            'site_id' => $this->request->get('site_id'),
            'real_name' => $this->request->get('real_name'),
            'mobile' => $this->request->get('mobile'),
            'status' => $this->request->get('status'),
            'id_number' => $this->request->get('id_number'),
            'front_image' => $this->request->get('front_image'),
            'back_image' => $this->request->get('back_image')
        ];

        app(RecyclerService::class)->updateRecycler($id, $data);

        return $this->success();
    }

    public function changeWork()
    {
        $id = $this->request->get('recycler_id');
        $status = $this->request->get('status');

        app(RecyclerService::class)->updateRecycler($id, ['status' => $status]);

        return $this->success();
    }

    public function deleteRecycler()
    {
        $id = $this->request->get('recycler_id');
        $userId = $this->request->get('user_id');
        app(RecyclerService::class)->deleteRecycler($id, $userId);

        return $this->success();
    }
}
