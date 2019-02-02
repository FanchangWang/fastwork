<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/1
 * Time: 19:06
 */

namespace fastwork;


use fastwork\exception\FileNotFoundException;

class Response
{
    /**
     * @var array 响应头
     */
    protected $header = [
        'Content-type' => 'text/html;charset=utf-8'
    ];
    /**
     * @var Request
     */
    protected $httpRequest;

    /**
     * @var \swoole_http_response
     */
    protected $httpResponse;

    public function __construct(Request $request)
    {
        $this->httpRequest = $request;
    }

    public static function __make(Request $request)
    {
        return new static($request);
    }


    public function setHttpResponse(\swoole_http_response $response)
    {
        $this->httpResponse = $response;
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
                $data = \json_encode($data, JSON_UNESCAPED_UNICODE);
            }
            return $data;
        }
    }

    /**
     * 设置响应头
     * @param $key
     * @param $val
     * @return Response
     */
    public function header($key, $val)
    {
        $key = ucfirst($key);
        $this->header[$key] = $val;
        return $this;
    }

    /**
     * 设置cookie
     * @param mixed ...$args
     */
    public function cookie(...$args)
    {
        $this->httpResponse->cookie(...$args);
    }

    public function code($code)
    {
        $this->httpResponse->status($code);
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
    public function tpl(array $data = [], $file)
    {
        if ($this->httpRequest->isJson()) {
            $this->header('Content-type', 'application/json');
            return format_json($data, 1, $this->httpRequest->id());
        } else {
            if (!file_exists($file)) {
                throw new FileNotFoundException('未定义模板路径:' . $file, 404);
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

    /**
     * @return Request
     */
    public function getHttpRequest(): Request
    {
        return $this->httpRequest;
    }

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }
}