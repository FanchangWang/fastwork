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
        for ($i = 1; $i <= 10; $i++) {
            Redis::set('de_' . $i, $i ,300);
        }
    }
}