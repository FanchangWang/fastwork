<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 15:24
 */

namespace App\index\controller;

use Core\Request;

class Index
{


    public function index(Request $request)
    {
        $action = $request->param();
        return $action;
    }
}