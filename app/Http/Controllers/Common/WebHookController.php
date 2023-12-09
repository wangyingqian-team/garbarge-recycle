<?php
namespace App\Http\Controllers\Common;

use App\Http\Controllers\Official\BaseController;
use Illuminate\Support\Facades\Log;

class WebHookController extends BaseController
{
    public function pushEvent()
    {
        $path = '/www/wwwroot/www.garbage-recycle.com/garbarge-recycle';
        $branch = 'garbage_1.0';
        $res = shell_exec("cd {$path} && git pull origin {$branch} 2>&1");

        // 打印拉取log
        Log::channel('default')->info('web hook res: ' . $res);

        return $this->success([
            'result' => $res,
            'msg' => 'ok'
        ]);
    }
}
