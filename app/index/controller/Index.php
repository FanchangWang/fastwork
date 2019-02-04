<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/2
 * Time: 9:19
 */

namespace app\index\controller;


use fastwork\Controller;
use fastwork\facades\Session;

class Index extends Controller
{

    public function index()
    {
        Session::set('dsadsa',111);
        return $this->success(1111);
    }
}