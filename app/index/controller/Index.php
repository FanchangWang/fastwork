<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/2
 * Time: 9:19
 */

namespace app\index\controller;


use fastwork\Controller;
use fastwork\Db;
use fastwork\facades\Cache;
use fastwork\facades\Redis;

class Index extends Controller
{

    public function index()
    {
        go(function () {
            Cache::set('redis', 1111);
        });
        return $this->success();
    }
}