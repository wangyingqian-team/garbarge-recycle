<?php

namespace App\Services\Common;

use App\Exceptions\RestfulException;
use Illuminate\Support\Arr;
use JohnLui\AliyunOSS;

/**
 * 阿里 oss 服务
 *
 * Class AliOssService
 *
 * @package App\Services\Common
 */
class AliOssService
{
    /**
     * 允许的后缀名
     *
     * @var array
     */
    protected $allowExt = [
        'jpg',
        'png',
        'jpeg',
        'bmp', //图片
    ];

    protected $prefix = 'default';

    protected $bucket;

    protected $options = [];

    protected $ossClient;

    /**
     * 私有初始化 API，非 API，不用关注
     * @param boolean 是否使用内网
     */
    public function __construct($isInternal = false)
    {
        if (config('oss.ali_oss.type') == 'VPC' && !$isInternal) {
            throw new RestfulException("VPC 网络下不提供外网上传、下载等功能");
        }

        $this->bucket = config('oss.bucket_name');

        $this->ossClient = AliyunOSS::boot(
            config('oss.ali_oss.city'),
            config('oss.ali_oss.type'),
            $isInternal,
            config('oss.ali_oss.access_key'),
            config('oss.ali_oss.access_secret')
        );

        $this->ossClient->setBucket($this->bucket);
    }

    /**
     * 上传文件
     *
     * @param $files
     * @param $first
     *
     * @return array
     */
    public function upload($files)
    {
        $urls = [];
        $files = Arr::wrap($files);

        foreach ($files as $file) {
            $ext = get_ext($file->getClientOriginalName());
            if (!in_array($ext, $this->allowExt)) {
                throw new RestfulException('该类型不允许上传');
            }

            $filename = $this->prefix . '/' . get_unique_name() . '.' . $ext;

            try {
                $this->ossClient->uploadFile($filename, $file, $this->options);
                $urls[] = $this->ossClient->getPublicUrl($filename);
            } catch (\Throwable $e) {
                throw new RestfulException($e->getMessage());
            }
        }

        return $urls;
    }


    public function getUrl($url)
    {
        return $this->ossClient->getPublicUrl($url);
    }

    public function delete()
    {
    }

    public function setBucketName($bucketName)
    {
        $this->bucket = $bucketName;

        return $this;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }
}
