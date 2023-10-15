<?php

namespace App\Http\Controllers\Common;

use App\Exceptions\RestfulException;
use App\Http\Controllers\Controller;
use App\Services\Common\AliOssService;
use App\Supports\Constant\ImageTypeConst;
use Illuminate\Support\Arr;

class ImageController extends Controller
{
    /** @var  AliOssService */
    protected $service;

    public function init()
    {
        $this->service = app(AliOssService::class);
    }

    /**
     * 上传图片
     *
     * @return mixed
     */
    public function upload()
    {
        $paths = [];
        $images = Arr::wrap($this->request->file('images'));
        $type = $this->request->get('type');
        $types = ImageTypeConst::IMAGE_TYPE_PATH_MAP;
        if (!isset($types[$type])) {
            throw new RestfulException('图片类型错误');
        }

        foreach ($images as $image) {
            $paths[] = $this->service->setPrefix($types[$type])->upload($image);
        }

        return $this->success($paths);
    }
}
