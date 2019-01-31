<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 12:48
 */

namespace Traits;


class JsonResult
{

    public function error($msg = '操作失败', $data = '')
    {
        return $this->result(0, $msg, $data);

    }

    public function success($msg = '操作成功', $data = '')
    {
        return $this->result(1, $msg, $data);
    }

    public function result($code, $msg = '', $data = '')
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
        return json_encode($result);
    }

}