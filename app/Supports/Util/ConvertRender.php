<?php

namespace App\Supports\Util;

use Illuminate\Validation\ValidationException;

class ConvertRender
{
    public static $status = 200;

    public static $message;

    public static $code;

    /**
     * 转换http异常
     *
     * @param $e
     */
    public static function convertHttpException($e)
    {
        return $e;
    }

    /**
     * 转换 restful 异常
     *
     * @param $e
     *
     * @return mixed
     */
    public static function convertRestfulException($e)
    {
        self::$status = 200;

        return $e;
    }

    /**
     * 转换验证器异常
     *
     * @param $e
     *
     * @return mixed
     */
    public static function convertValidationException(ValidationException $e)
    {
        self::$message = array_reduce($e->errors(), function ($result, $v) {
            $result .= implode(';', $v) . ';';
            return $result;
        });
        return $e;
    }


    /**
     * 转换其他异常
     *
     * @param $e
     *
     * @return mixed
     */
    public static function convertOtherException($e)
    {
        return $e;
    }

    public static function toResponse(\Throwable $e)
    {
        return [
            'message' => self::$message ?: $e->getMessage(),
            'code' => self::$code ?: $e->getCode(),
            'status' => self::$status,
            'trance' => self::getTrance($e),
        ];
    }

    protected static function getTrance(\Throwable $e)
    {
        $tranceString = $e->getTraceAsString();
        $trance = explode("\n", $tranceString);

        return [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trance' => $trance,
            'previous' => $e->getPrevious()
        ];
    }
}
