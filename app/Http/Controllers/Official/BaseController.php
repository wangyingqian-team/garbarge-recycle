<?php
/**
 * Created by PhpStorm.
 * User: wumx2
 * Date: 2020-3-18
 * Time: 15:41
 */

namespace App\Http\Controllers\Official;


use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    protected $userId;

    protected $recyclerId;

    protected $page;

    protected $pageSize;

    public function init()
    {
        $this->userId = $this->request->input("user_id", 0);
        $this->recyclerId = $this->request->input('recycler_id', 0);
        $this->page = $this->request->get('page', 0);
        $this->pageSize = $this->request->get('page_size', 0);
    }
}
