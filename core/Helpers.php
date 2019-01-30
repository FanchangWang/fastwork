<?php

use Core\facade\Config;

/**
 * @param bool $base62
 * @return string
 * @throws Exception
 */
function uuid($base62 = true)
{
    $str = uniqid('', true);
    $arr = explode('.', $str);
    $str = $arr[0] . base_convert($arr[1], 10, 16);
    $len = 32;
    while (strlen($str) <= $len) {
        $str .= bin2hex(random_bytes(4));
    }
    $str = substr($str, 0, $len);
    if ($base62) {
        $str = str_replace(['+', '/', '='], '', base64_encode(hex2bin($str)));
    }
    return $str;
}


/**
 * @param array $arr
 * @param $key
 * @return mixed|null
 */
function array_get($arr, $key, $default = null)
{
    if (isset($arr[$key])) {
        return $arr[$key];
    } else if (strpos($key, '.') !== false) {
        $keys = explode('.', $key);
        foreach ($keys as $v) {
            if (isset($arr[$v])) {
                $arr = $arr[$v];
            } else {
                return $default;
            }
        }
        return $arr;
    } else {
        return $default;
    }
}

/**
 * @param $str
 * @param null $allow_tags
 * @return string
 */
function filter_xss($str, $allow_tags = null)
{
    $str = strip_tags($str, $allow_tags);
    if ($allow_tags !== null) {
        while (true) {
            $l = strlen($str);
            $str = preg_replace('/(<[^>]+?)([\'\"\s]+on[a-z]+)([^<>]+>)/i', '$1$3', $str);
            $str = preg_replace('/(<[^>]+?)(javascript\:)([^<>]+>)/i', '$1$3', $str);
            if (strlen($str) == $l) {
                break;
            }
        }
    }
    return $str;
}

if (!function_exists('config')) {
    /**
     * 获取和设置配置参数
     * @param string|array $name 参数名
     * @param mixed $value 参数值
     * @return mixed
     */
    function config($name = '', $value = null)
    {
        if (is_string($name)) {
            if ('.' == substr($name, -1)) {
                return Config::pull(substr($name, 0, -1));
            }

            return 0 === strpos($name, '?') ? Config::has(substr($name, 1)) : Config::get($name);
        }

        return $value;
    }
}