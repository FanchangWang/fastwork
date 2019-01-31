<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 13:49
 */

namespace Core;

class Route
{

    public function http(Request $request, Config $config)
    {
        $param = [];
        $config = $config->get('app');
        $module = $config['default_module'];
        $controller = $config['default_controller'];
        $action = $config['default_action'];

        $request_uri = $request->server('request_uri');

        if (empty($request_uri)) {
            return ['m' => $module, 'c' => $controller, 'a' => $action, 'p' => $param];
        }

        $path = trim($request_uri, '/');

        $param = explode("/", $path);
        !empty($param[0]) && $module = $param[0];
        isset($param[1]) && $controller = $param[1];
        isset($param[2]) && $action = $param[2];

        if (count($param) >= 3) {
            $param = array_slice($param, 3);
        } else {
            $param = array_slice($param, 2);
        }

        $request->setModule($module)->setController($controller)->setAction($action);

        $params = [];
        if (!empty($param)) {
            foreach ($param as $key => $value) {
                if ($key % 2 == 0) {
                    $params[$value] = $key;
                } else {
                    $k = array_search($key - 1, $params);
                    isset($params[$k]) && $params[$k] = $value;
                }
            }
        }

        $request->setParam($params);

        return ['m' => $module, 'c' => $controller, 'a' => $action, 'p' => $params];
    }
}