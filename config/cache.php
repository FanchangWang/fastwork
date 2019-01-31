<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 12:40
 */

return [
    'poolMin' => 2,
    'poolMax' => 100, // 连接池最大数量
    'host' => '127.0.0.1',
    //密码
    'auth' => '',
    'port' => 6379,
    'prefix' => 'mzhua_',
    //清除空闲链接的定时器，默认60s
    'clearTime' => 60000,
    //空闲多久清空所有连接,默认300s
    'clearAll' => 300,
    //设置是否返回结果
    'setDefer' => true,
    //options配置
    'connect_timeout' => 2, //连接超时时间，默认为1s
    'timeout' => 5, //超时时间，默认为1s
    'serialize' => true, //自动序列化，默认false
    'reconnect' => 2  //自动连接尝试次数，默认为1次
];