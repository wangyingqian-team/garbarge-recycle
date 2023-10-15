<?php

namespace App\Dto;

use App\Models\ConfigModel;

class ConfigDto extends Dto
{
    public $model = ConfigModel::class;

    public function setConfig($key, $value)
    {
        $this->query->updateOrInsert(['key' => $key], ['value' => $value]);
        return true;
    }

    public function getConfig($key)
    {
        $value = $this->query->where(['key' => $key])->macroFirst();
        return $value['value']['value'] ?? null;
    }
}
