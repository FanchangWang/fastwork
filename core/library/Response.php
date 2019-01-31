<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 15:25
 */

namespace Core;

class Response
{
    /**
     * @var \swoole_http_response
     */
    private $httpResponse;

    /**
     * @var Request
     */
    protected $httpRequest;

    public function __construct(Request $request)
    {
        $this->httpRequest = $request;
    }

    public function setRespone(\swoole_http_response $response)
    {

        $this->httpResponse = $response;
        return $this;
    }

    /**
     * @param Request $httpRequest
     * @return Response
     */
    public function setHttpRequest(Request $httpRequest)
    {
        $this->httpRequest = $httpRequest;
        return $this;
    }

    /**
     * @return \swoole_http_response
     */
    public function getHttpResponse(): \swoole_http_response
    {
        return $this->httpResponse;
    }

    public static function __make(Request $request)
    {
        $request = new static($request);
        return $request;
    }

    /**
     * @param string $data
     * @param null|string $callback
     * @return string
     */
    public function json($data, $callback = null)
    {
        $this->header('Content-type', 'application/json');
        if ($callback) {
            return $callback . '(' . $data . ')';
        } else {
            if (is_array($data)) {
                $data = \json_encode($data);
            }
            return $data;
        }
    }

    public function header($key, $val, $code = null)
    {
        $this->httpResponse->header($key, $val);
        if ($code) {
            $this->code($code);
        }
    }

    /**
     * 设置响应头
     * @param $code
     */
    public function code($code)
    {
        $this->httpResponse->status($code);
    }

    public function cookie(...$args)
    {
        $this->httpResponse->cookie(...$args);
    }

    /**
     * 页面跳转
     * @param $url
     * @param array $args
     * @return string
     */
    public function redirect($url, $code = 302)
    {
        $this->httpResponse->redirect($url, $code);
    }

    /**
     * @param string $file
     * @param array $data
     * @return string
     * @throws \HttpResponseException
     */
    public function tpl($file, array $data = [])
    {
        if ($this->httpRequest->isJson()) {
            $this->header('Content-type', 'application/json');
            return format_json($data, 1, $this->httpRequest->id());
        } else {
            if (!file_exists($file)) {
                throw new \HttpResponseException('未定义模板路径:' . $file, 404);
            }
            ob_start();
            extract($data);
            require $file;
            return ob_get_clean();
        }
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->httpResponse, $name)) {
            return $this->httpResponse->$name(...$arguments);
        }
    }

}