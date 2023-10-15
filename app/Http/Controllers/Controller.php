<?php

namespace App\Http\Controllers;

use App\Services\Common\SpreadExcelService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->init();
    }

    public function init()
    {

    }

    public function success($data = true, $message = 'ok', $code = 200, $status = 200)
    {
        return response()->macroSuccess($data, $code, $message, $status);
    }

    public function test()
    {

        return $this->success('test');
    }

    private function exportTest(){
        $cells = [
            'id' => 'id',
            'name' => '姓名',
            'age' => '年龄',
            'sex' => '性别'
        ];

        $a = [
            [
                "id" => 1,
                "name" => "里斯",
                "age" => 1384587952,
                "sex" => "男"
            ],
            [
                "id" => 2,
                "name" => "高富帅",
                "age" => 14,
                "sex" => "男"
            ],
            [
                "id" => 3,
                "name" => "给i哦呀戈萨夫官方萨格岁的法国事故发生结果格式符合监管和发送给供货商风格恢复刚刚还说封建快攻",
                "age" => 'WS20200717144550000004368',
                "sex" => "女"
            ]
        ];

        $s = new SpreadExcelService();
        $s->setCell($cells)->setTitle('test')->export($a);

        return $this->success($a);
    }
}
