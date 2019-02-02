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
use fastwork\Request;

class Index extends Controller
{

    public function index(Request $request)
    {
        $date['start'] = microtime(true);
        for ($i=1;$i<10;$i++){
            go(function ()use ($i){
                Redis::setDefer(false)->set('redis_'.$i,1);
            });
        }
        $date['end'] = microtime(true);
        return $date;
    }
}