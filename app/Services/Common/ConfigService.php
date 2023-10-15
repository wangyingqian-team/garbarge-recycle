<?php
namespace App\Services\Common;

use App\Dto\ConfigDto;

class ConfigService {
    public function setConfig ($key, $value) {
        return app(ConfigDto::class)->setConfig($key, json_encode(['value'=>$value], JSON_UNESCAPED_UNICODE));
    }

    public function getConfig($key) {
        return app(ConfigDto::class)->getConfig($key);
    }
}
