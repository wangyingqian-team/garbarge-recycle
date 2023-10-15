<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GarbageRecycle\GarbageSiteService;

class BaseController extends Controller
{
    public $adminId;

    public $siteId;

    protected $page;

    protected $pageSize;


    public function init()
    {
        $this->adminId = $this->request->header('adminId', 0);

        $this->siteId = app(GarbageSiteService::class)->getGarbageIdByAdminId($this->adminId);

        $this->page = $this->request->get('page', 0);
        $this->pageSize = $this->request->get('page_size', 0);
    }
}
