<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/2
 * Time: 9:19
 */

namespace app\index\controller;


use fastwork\Controller;
use fastwork\facades\Redis;

class Index extends Controller
{

    public function index()
    {
        for ($i = 1; $i <= 500; $i++) {
            go(function () use ($i){
                Redis::set('redis_'.$i,$i);
            });
        }
        return $this->success(1);
    }
}