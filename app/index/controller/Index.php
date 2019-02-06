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
use Swoole\Coroutine\MySQL;
use Swoole\Coroutine\MySQL\Exception;

class Index extends Controller
{

    public function index()
    {

        $result = Db::transaction(function (MySQL $db, \chan $chan) {
            $stmt = $db->prepare('SELECT * FROM mz_user WHERE uid=?');
            if ($stmt == false) {
                $chan->push($db->errno);
            } else {
                $info = $stmt->execute([1]);
                $chan->push(json_encode($info));
                $db->commit();
            }
        }, function (MySQL $db, \chan $chan) {
            $db->rollback();
            $chan->push('error');
        });

        return $result;

    }
}