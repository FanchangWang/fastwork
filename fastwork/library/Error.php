<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/1
 * Time: 22:39
 */

namespace fastwork;


use fastwork\exception\HttpRuntimeException;

use fastwork\facades\Config;
use fastwork\facades\Log;
class Error
{
    public function render(Response $response, HttpRuntimeException $e)
    {

        $response->code($e->getCode());
        self::report($e);
        if ($response->getHttpRequest()->isJson()) {
            return $response->json(format_json($e->getMessage(), $e->getCode(), $response->getHttpRequest()->id()));
        } else {
            $file = Config::get('app.http_exception_template');
            if (file_exists($file)) {
                return $response->tpl(['e' => $e], $file);
            } else {
                return $response->json(format_json($e->getMessage(), $e->getCode(), $response->getHttpRequest()->id()));
            }
        }
    }

    public function report(\Throwable $e)
    {
        Log::error([
            'file' => $e->getFile() . ':' . $e->getLine(),
            'msg' => $e->getMessage(),
            'code' => $e->getCode(),
            'trace' => $e->getTrace()
        ]);
    }
}