<?php
namespace App\Http\Controllers\Admin;


use App\Services\Common\ConfigService;

class ConfigController extends BaseController
{
    /** @var ConfigService */
    protected $service;

    public function init()
    {
        $this->service = app(ConfigService::class);
    }

    public function getConfig()
    {
        $key = $this->request->get('key');

        $value = $this->service->getConfig($key);

        return $this->success($value);
    }

    public function setConfig()
    {
        $key = $this->request->get('key');
        $value = $this->request->get('value');

        $result = $this->service->setConfig($key, $value);

        return $this->success($result);
    }

}
