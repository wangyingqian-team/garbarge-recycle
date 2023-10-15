<?php
namespace App\Exceptions;

use App\Supports\Constant\ExceptionCode;
use Throwable;

class RestfulException extends \RuntimeException
{
    public function __construct($message = "", $code = ExceptionCode::RESTFUL_EXCEPTION_CODE, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
