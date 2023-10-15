<?php

namespace App\Http\Controllers\Admin;


use App\Exceptions\RestfulException;
use App\Services\Admin\AdminService;

class AdminController extends BaseController
{
    /** @var AdminService */
    protected $service;

    public function init()
    {
        $this->service = app(AdminService::class);
    }

    public function register()
    {
        $recommender = $this->request->get('recommander');
        if ($recommender != '0566') {
            throw new RestfulException('推荐人错误');
        }

        $this->validate(
            $this->request,
            [
                'name' => 'required',
                'password' => 'required|min:6',
                'role_id' => 'required'
            ]
        );

        $data = [
            'name' => $this->request->get('name'),
            'password' => $this->request->get('password'),
            'role_id' => $this->request->get('role_id'),
            'status' => $this->request->get('status')
        ];


        return $this->success($this->service->register($data));
    }

    public function login()
    {
        $name = $this->request->get('name');
        $password = $this->request->get('password');

        return $this->success($this->service->login($name, $password));
    }

    public function index()
    {
        return $this->success('aaa');
    }
}
