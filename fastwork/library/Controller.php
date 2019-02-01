<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/1
 * Time: 19:05
 */

namespace fastwork;


use fastwork\exception\FileNotFoundException;

class Controller
{
    /**
     * @var Fastwork
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

    /**
     * 需要传递的assgin值
     * @var array
     */
    protected $assign = [];

    public function __construct(Fastwork $app)
    {

        $this->app = $app;
        $this->request = $this->app->request;
        $this->response = $this->app->response;
        $this->init();
    }

    protected function init()
    {
    }

    /**
     * 跳转
     * @param $url
     * @return string
     */
    public function redirect($url)
    {
        return $this->response->redirect($url, 302);
    }

    /**
     * @param $key
     * @param $val
     * @return Controller
     */
    public function assign($key, $val)
    {
        $this->assign[$key] = $val;
        return $this;
    }

    /**
     * 渲染模板
     * @param $file
     * @param array $var
     * @return string
     * @throws \HttpResponseException
     */
    public function fetch($file = null, $var = [])
    {
        $module = $this->request->module();
        $controller = $this->request->controller();
        $action = $this->request->action();
        $param = explode('/', $file, 3);
        !empty($param[0]) && $module = $param[0];
        isset($param[1]) && $controller = $param[1];
        isset($param[2]) && $action = $param[2];

        $ext = $this->app->env->get('config_ext', '.php');

        $path = $this->app->env->get('app_path') . $module . '/view/' . $controller . '/' . $action . $ext;
        if (!is_file($path)) {
            throw new FileNotFoundException("template not exist: " . $path);
        }
        if (!empty($var)) {
            $this->assign = array_merge($this->assign, $var);
        }
        return $this->response->tpl($this->assign, $path);
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->assign = [];
    }
}