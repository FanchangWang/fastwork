<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/2
 * Time: 9:19
 */

namespace app\index\controller;


use fastwork\Controller;
use fastwork\facades\Cache;

class Index extends Controller
{

    public function index()
    {
        $date['start'] = microtime(true);
        for ($i = 1; $i < 100; $i++) {
            Cache::set('redis_' . $i, $i);
            echo $i . PHP_EOL;
        }
        go(function () {
            echo '1' . PHP_EOL;
        });

        go(function () {
            sleep(1);
            echo '2' . PHP_EOL;
        });
        echo '3'.PHP_EOL;
        $date['end'] = microtime(true);
        $date['more'] = bcsub($date['end'], $date['start'], 10);
        return $date;
    }
}