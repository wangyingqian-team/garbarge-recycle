<?php
namespace App\Http\Controllers\Admin;

use App\Services\Mass\Activity;
use App\Supports\Constant\ServiceConst;

class ActivityController extends BaseController
{
    /** @var Activity */
    protected $service;

    public function init()
    {
        $this->service = get_driver(ServiceConst::MASS_MANAGER, ServiceConst::MASS_ACTIVITY);
    }

    public function getActivityList()
    {
        $title = $this->request->get('title');
        $page = $this->request->get('page',1);
        $limit = $this->request->get('page_num', 15);

        $where = [];
        if (!empty($title)){
            $where['title|like'] = '%'.$title.'%';
        }

        $list = $this->service->getActivityList($where, ['*'], ['create_at'=>'desc'], $page, $limit);

        return $this->success($list);
    }

    public function getActivityInfo()
    {
        $ActivityId = $this->request->get('id');

        $info = $this->service->getActivityInfo($ActivityId);

        return $this->success($info);
    }

    public function addActivity()
    {
        $title = $this->request->post('title');
        $cover = $this->request->post('cover');
        $writer = $this->request->post('writer');
        $content = $this->request->post('content');
        $data = [
            'title' => $title,
            'writer' => $writer,
            'content' => $content,
            'cover' => $cover
        ];
        $this->service->addActivity($data);

        return $this->success();
    }

    public function editActivity()
    {
        $ActivityId = $this->request->post('id');
        $title = $this->request->post('title');
        $writer = $this->request->post('writer');
        $content = $this->request->post('content');
        $cover = $this->request->post('cover');
        $data = [
            'title' => $title,
            'writer' => $writer,
            'content' => $content,
            'cover' => $cover
        ];

        $data = array_filter($data);

        $this->service->editActivity($ActivityId, $data);

        return $this->success();
    }

    public function delActivity()
    {
        $ActivityId = $this->request->post('id');
        $this->service->deleteActivity($ActivityId);


        return $this->success();
    }
}