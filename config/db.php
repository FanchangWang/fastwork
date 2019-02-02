<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/2
 * Time: 12:57
 */

return [
    'mysql' => [
        'max_connect_count' => 10, // 连接池最大连接的数量
        'dns' => 'mysql:host=127.0.0.1;dbname=test',
        'username' => 'root',
        'password' => '123456',
        'ops' => [ // pdo 相关设置
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_PERSISTENT => false //协程环境必须为false
        ]
    ]
];