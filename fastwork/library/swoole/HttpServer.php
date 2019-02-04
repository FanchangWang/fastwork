<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/1
 * Time: 19:36
 */

namespace fastwork\swoole;


use fastwork\exception\ClassNotFoundException;
use fastwork\exception\MethodNotFoundException;
use fastwork\facades\Error;
use fastwork\exception\HttpRuntimeException;

class HttpServer extends Server
{

    /**
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     * @return mixed
     */
    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {

        if ($request->server['request_uri'] == '/favicon.ico') {
            return $response->end();
        };

        $this->app->request->setHttpRequest($request);
        $this->app->response->setHttpResponse($response);

        try {
            $router = $this->app->route->dispath(
                $this->app->request
            );
        } catch (HttpRuntimeException $e) {
            $router = Error::render($this->app->response, $e);
        } catch (\Throwable $e) {
            $router = '';
            Error::report($e);
        }

        foreach ($this->app->response->getHeader() as $k => $v) {
            $response->header($k, $v);
        }
        //清除缓存
        $this->app->response->clear();

        return $response->end($router);
    }
}