<?php

namespace App\Supports\Macro;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response as FacadeResponse;

class Response implements MacroInterface
{
    public function macroSuccess()
    {
        return function ($data = [], $code = 200, $message = '', $status = 200) {
            $content = [
                'code' => $code,
                'message' => $message,
                'data' => $data
            ];

            return $this->json($content, $status, [], config('app.debug') ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE : JSON_UNESCAPED_UNICODE);
        };
    }

    public function extend()
    {
        $macros = get_class_methods($this);

        foreach ($macros as $macro) {
            if (Str::startsWith($macro, 'macro')) {
                FacadeResponse::macro($macro, $this->{$macro}());
            }
        }
    }

}
