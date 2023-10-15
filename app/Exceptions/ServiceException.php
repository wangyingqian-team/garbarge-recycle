<?php
namespace App\Exceptions;

use App\Supports\Constant\ExceptionCode;
use Throwable;

class ServiceException extends RestfulException
{
    public function __construct($message = "", $code = ExceptionCode::SERVICE_EXCEPTION_CODE, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
