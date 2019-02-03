<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/1
 * Time: 19:05
 */

namespace fastwork;


use fastwork\exception\FileNotFoundException;
use traits\JsonResult;

class Controller
{
    use JsonResult;
    /**
     * @var Fastwork
     */
    protected $app;
    /**
     * @var Request
     */
    protected $request;

    private $response;
    /**
     * 需要传递的assgin值
     * @var array
     */
    private $assign = [];

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
    protected function redirect($url)
    {
        return $this->response->redirect($url, 302);
    }

    /**
     * @param $key
     * @param $val
     * @return Controller
     */
    protected function assign($key, $val)
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
    protected function fetch($file = null, $var = [])
    {
        $module = $this->request->module();
        $controller = $this->request->controller();
        $action = $this->request->action();
        if (strpos($file, '/') !== false) {
            $param = explode('/', $file, 3);
            if (count($param) == 2) {
                $controller = $param[0];
                $action = $param[1];
            } else {
                $module = $param[0];
                $controller = $param[1];
                $action = $param[2];
            }
        } else {
            if (!is_null($file)) {
                $action = $file;
            }
        }
        $ext = $this->app->env->get('config_ext', '.php');
        $path = $this->app->env->get('app_path') . $module . '/view/' . ucfirst($controller) . '/' . $action . $ext;
        if (!is_file($path)) {
            throw new FileNotFoundException("template not exist: " . $path);
        }
        $assign = $this->getAssign();
        if (!empty($var)) {
            $assign = array_merge($assign, $var);
        }
        return $this->response->tpl($assign, $path);
    }

    private function getAssign()
    {
        $assign = $this->assign;
        $this->assign = [];
        return $assign;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->assign = [];
    }
}