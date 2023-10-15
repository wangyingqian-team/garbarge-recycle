<?php

namespace App\Exceptions;

use App\Supports\Util\ConvertRender;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception
     * @param Exception $exception
     * @return mixed|void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        $convertResponse = ConvertRender::toResponse($this->getException($e));

        return response()->macroSuccess(
            config('app.debug') ? $convertResponse['trance'] : '',
            $convertResponse['code'],
            $convertResponse['message'],
            $convertResponse['status']
        );
    }

    /**
     * 获取 restful 异常
     *
     * @param $e
     *
     * @return mixed
     */
    protected function getException($e)
    {
        if ($e instanceof NotFoundHttpException | $e instanceof MethodNotAllowedException) {
            $e = ConvertRender::convertHttpException($e);
        } elseif ($e instanceof RestfulException) {
            $e = ConvertRender::convertRestfulException($e);
        } elseif ($e instanceof ValidationException) {
            $e = ConvertRender::convertValidationException($e);
        } else {
            $e = ConvertRender::convertOtherException($e);
        }

        return $e;
    }

}
