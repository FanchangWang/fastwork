<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 12:44
 */

namespace Core;


class Controller
{

    /**
     * @var App
     */
    protected $app;
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response = null;

    public function __construct(App $app, Request $request)
    {

        $this->app = $app;
        $this->request = $request;
        $this->response = $this->app['response'];
        $this->init();
    }

    protected function init()
    {
    }
}