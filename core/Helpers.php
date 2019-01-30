<?php
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