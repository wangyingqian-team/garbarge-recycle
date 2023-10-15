<?php
namespace App\Exceptions;

use App\Supports\Constant\ExceptionCode;
use Throwable;

/**
 * 验证规则异常
 *
 * Class ValidateException
 *
 * @package App\Exceptions
 */
class ValidateException extends RestfulException
{
    public function __construct($message = "", $code = ExceptionCode::VALIDATE_EXCEPTION_CODE, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
