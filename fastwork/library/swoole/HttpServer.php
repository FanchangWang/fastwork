<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/1
 * Time: 19:36
 */

namespace fastwork\swoole;


class HttpServer extends Server
{

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {

        if ($request->server['request_uri'] == '/favicon.ico') {
            return $response->end();
        };


    }
}