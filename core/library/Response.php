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
     * @var Request
     */
    protected $httpRequest;

    protected $_session = null;

    /**
     * @var \swoole_http_response
     */
    protected $response;


    public function __construct(Request $request)
    {
        $this->httpRequest = $request;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    public function cookie(string $name, $value, $expire, $path, $domain, $secure = null, $httponly = null)
    {
        $this->response->cookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

}