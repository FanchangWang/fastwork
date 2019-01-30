<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 12:44
 */

namespace Core;


class Request
{
    protected $server = [];

    protected $cookie = [];

    protected $get = [];

    protected $post = [];

    protected $files = [];

    protected $request = [];

    public $fd = 0;

    public $method = '';

    /**
     * 全局过滤规则
     * @var array
     */
    protected $filter;
    /**
     * @var \swoole_http_request
     */
    private $httpRequest;

    public function setRequest(\swoole_http_request $request)
    {
        foreach ($request->server as $k => $v) {
            $this->server[str_replace('-', '_', strtoupper($k))] = $v;
        }
        foreach ($request->header as $k => $v) {
            $this->server['HTTP_' . str_replace('-', '_', strtoupper($k))] = $v;
        }
        $this->fd = $request->fd;
        $this->cookie = $request->cookie ?? [];
        $this->get = $request->get ?? [];
        $this->post = $request->post ?? [];
        $this->files = &$request->files;
        $this->request = $this->get + $this->post;
        $this->httpRequest = $request;
    }

    /**
     * 获取服务头请求
     * @param $name
     * @return mixed|null
     */
    public function server($name = null, $default = null)
    {
        if (empty($name)) {
            return $this->server;
        } else {
            $name = strtoupper($name);
        }

        return isset($this->server[$name]) ? $this->server[$name] : $default;
    }

    /**
     * 获取服务头请求，兼容TP
     * @param null $name
     * @param null $default
     * @return mixed|null
     */
    public function header($name = null, $default = null)
    {
        return $this->server($name, $default);
    }

    /**
     * @return mixed|null
     */
    public function user_agent()
    {
        return $this->server('HTTP_USER_AGENT');
    }

    /**
     * 是否为GET请求
     * @access public
     * @return bool
     */
    public function isGet()
    {
        return $this->method() == 'GET';
    }

    /**
     * 是否为POST请求
     * @access public
     * @return bool
     */
    public function isPost()
    {
        return $this->method() == 'POST';
    }

    /**
     * 是否为PUT请求
     * @access public
     * @return bool
     */
    public function isPut()
    {
        return $this->method() == 'PUT';
    }

    /**
     * 是否为DELTE请求
     * @access public
     * @return bool
     */
    public function isDelete()
    {
        return $this->method() == 'DELETE';
    }

    /**
     * 是否为HEAD请求
     * @access public
     * @return bool
     */
    public function isHead()
    {
        return $this->method() == 'HEAD';
    }

    /**
     * 是否为PATCH请求
     * @access public
     * @return bool
     */
    public function isPatch()
    {
        return $this->method() == 'PATCH';
    }

    /**
     * 是否为OPTIONS请求
     * @access public
     * @return bool
     */
    public function isOptions()
    {
        return $this->method() == 'OPTIONS';
    }

    /**
     * 是否为cli
     * @access public
     * @return bool
     */
    public function isCli()
    {
        return PHP_SAPI == 'cli';
    }

    /**
     * 是否为cgi
     * @access public
     * @return bool
     */
    public function isCgi()
    {
        return strpos(PHP_SAPI, 'cgi') === 0;
    }

    /**
     * @return array
     */
    public function file()
    {
        $files = [];
        foreach ($this->files as $name => $fs) {
            $keys = array_keys($fs);
            if (is_array($fs[$keys[0]])) {
                foreach ($keys as $k => $v) {
                    foreach ($fs[$v] as $name => $val) {
                        $files[$name][$v] = $val;
                    }
                }
            } else {
                $files[$name] = $fs;
            }
        }
        return $files;
    }

    /**
     * 获取请求类型
     * @return string
     */
    public function method()
    {
        return strtolower($this->server('REQUEST_METHOD'));
    }

    /**
     * 是否是json请求
     * @return bool
     */
    public function isJson()
    {
        if (strpos($this->server('HTTP_ACCEPT'), '/json') !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 当前是否Ajax请求
     * @access public
     * @return bool
     */
    private function isAJax()
    {
        $value = $this->server('HTTP_X_REQUESTED_WITH');
        $result = 'xmlhttprequest' == strtolower($value) ? true : false;
        return $result;
    }

    /**
     * 原始数据
     * @return mixed
     */
    public function inputRaw()
    {
        return $this->httpRequest->rawContent();
    }

    /**
     * 获取POST参数
     * @access public
     * @param  string|false $name 变量名
     * @param  mixed $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function post($name = '', $default = null, $filter = '')
    {
        if (empty($this->post)) {
            $this->post = !empty($this->inputRaw()) ? json_decode($this->inputRaw(), true) : [];
        }

        return $this->input($this->post, $name, $default, $filter);
    }

    /**
     * 获取GET参数
     * @access public
     * @param  string|false $name 变量名
     * @param  mixed $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function get($name = '', $default = null, $filter = '')
    {
        if (empty($this->get)) {
            $this->get = !empty($this->inputRaw()) ? json_decode($this->inputRaw(), true) : [];
        }

        return $this->input($this->get, $name, $default, $filter);
    }

    /**
     * @param $data
     * @param $name
     * @param $default
     * @param $filter
     * @return array
     */
    public function input($data, $name = '', $default = null, $filter = '')
    {
        if ('' === $name) {
            if ($this->isJson()) {
                $data = json_decode($this->inputRaw(), true);
            } else {
                $data = $this->request;
            }
            return $data;
        }
        $data = array_get($data, $name, $default);

        if (is_null($data)) {
            return $default;
        }
        // 解析过滤器
        $filter = $this->getFilter($filter, $default);

        if (is_array($data)) {
            array_walk_recursive($data, [$this, 'filterValue'], $filter);
            reset($data);
        } else {
            $this->filterValue($data, $name, $filter);
        }
        return $data;
    }


    /**
     * 获取任意参数
     * @param string $name
     * @param null $default
     * @param string $filter
     * @return array
     */
    public function param($name = '', $default = null, $filter = '')
    {

        $method = $this->method();

        // 自动获取请求变量
        switch ($method) {
            case 'POST':
                $vars = $this->post(false);
                break;
            default:
                $vars = $this->get(false);
        }

        if (true === $name) {
            // 获取包含文件上传信息的数组
            $file = $this->file();
            $data = is_array($file) ? array_merge($vars, $file) : $vars;

            return $this->input($data, '', $default, $filter);
        } else {
            $data = $vars;
        }


        return $this->input($data, $name, $default, $filter);
    }

    /**
     * 设置或获取当前的过滤规则
     * @access public
     * @param  mixed $filter 过滤规则
     * @return mixed
     */
    public function filter($filter = null)
    {
        if (is_null($filter)) {
            return $this->filter;
        }

        $this->filter = $filter;
    }


    protected function getFilter($filter, $default)
    {
        if (is_null($filter)) {
            $filter = [];
        } else {
            $filter = $filter ?: $this->filter;
            if (is_string($filter) && false === strpos($filter, '/')) {
                $filter = explode(',', $filter);
            } else {
                $filter = (array)$filter;
            }
        }

        $filter[] = $default;

        return $filter;
    }

    /**
     * 递归过滤给定的值
     * @access public
     * @param  mixed $value 键值
     * @param  mixed $key 键名
     * @param  array $filters 过滤方法+默认值
     * @return mixed
     */
    private function filterValue(&$value, $key, $filters)
    {
        $default = array_pop($filters);

        foreach ($filters as $filter) {
            if (is_callable($filter)) {
                // 调用函数或者方法过滤
                $value = call_user_func($filter, $value);
            } elseif (is_scalar($value)) {
                if (false !== strpos($filter, '/')) {
                    // 正则过滤
                    if (!preg_match($filter, $value)) {
                        // 匹配不成功返回默认值
                        $value = $default;
                        break;
                    }
                } elseif (!empty($filter)) {
                    // filter函数不存在时, 则使用filter_var进行过滤
                    // filter为非整形值时, 调用filter_id取得过滤id
                    $value = filter_var($value, is_int($filter) ? $filter : filter_id($filter));
                    if (false === $value) {
                        $value = $default;
                        break;
                    }
                }
            }
        }

        return $value;
    }

}