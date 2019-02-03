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

        $this->app->request->setHttpRequest($request);

        $router = $this->app->route->http($this->app->request, $this->app->config);
        $this->app->response->setHttpResponse($response);
        $app_namespace = $this->app->env->get('app_namespace');

        $module = $router['m'];
        $controller = ucfirst($router['c']);
        $action = $router['a'];
        $param = $router['p'];

        $this->app->init($module);
        $this->app->request->setModule($module)->setController($controller)->setAction($action)->setParam($param);

        $classname = "\\{$app_namespace}\\{$module}\\controller\\{$controller}";
        $content = '';
        try {
            if (!class_exists($classname)) {
                throw  new  ClassNotFoundException('class not exit:' . $classname);
            }
            $reflect = new \ReflectionClass($classname);
            $constructor = $reflect->getConstructor();
            $args = [];
            if ($constructor) {
                $args = $this->app->bindParams($constructor, []);
            }
            if (!$reflect->hasMethod($action)) {
                throw new MethodNotFoundException('method not exit:' . $action);
            }
            $method = $reflect->getMethod($action);
            if (!$method->isPublic()) {
                throw new MethodNotFoundException('method not exit:' . "\\{$module}\\{$controller}\\{$action}");
            }
            $content = $this->app->invokeMethod([$reflect->newInstanceArgs($args), $action], $param);
        } catch (HttpRuntimeException $exception) {
            $content = Error::render($this->app->response, $exception);
        } catch (\Exception $exception) {
            $content = Error::render($this->app->response, $exception);
        } catch (\Throwable $exception) {
            Error::report($exception);
        }
        if (is_array($content)) {
            $content = $this->app->response->json($content);
        }
        // 发送Header
        foreach ($this->app->response->getHeader() as $key => $val) {
            var_dump($key.$val);
            $response->header($key, $val);
        }
        //改进对验证码和图片输出的支持
        if (!empty($content)) {
            $response->write($content);
        }
        //清除响应头
        $this->app->response->clear();
        //返回响应
        return $response->end();
    }
}