<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/2
 * Time: 16:24
 */

namespace fastwork\facades;


use fastwork\Facade;
/**
 * @see \fastwork\Session
 * @mixin \fastwork\Session
 * @method bool has(string $name,string $prefix = null) static 判断session数据
 * @method mixed get(string $name = '',string $prefix = null) static session获取
 * @method void set(string $name, mixed $value , string $prefix = null) static 设置session数据
 * @method void delete(string $name, string $prefix = null) static 删除session数据
 * @method void clear($prefix = null) static 清空session数据
 * @method void destroy() static 销毁session
 * @method void regenerate(bool $delete = false) static 重新生成session_id
 */
class Session extends Facade
{
    protected static function getFacadeClass()
    {
        return 'session';
    }

}