<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 12:41
 */

namespace Core;


class Session
{
    private $data = [];

    private $name = '';

    private $session_id = '';

    private $time = 0;

    private $drive;

    private $prefix = 'session_';

    /**
     * Session constructor.
     * @param  Response $response
     * @param null $id session.id
     * @throws \Exception
     */
    public function __construct($response = null, $id = null)
    {
        $this->name = config('session.name');

        if ($id) {
            $this->session_id = $id;
        } else if ($response) {
            if (!$this->session_id) {
                $this->session_id = sha1(uuid());
            }
        }

        if (!$this->session_id) {
            return;
        }

        $this->time = intval(ini_get('session.gc_maxlifetime'));

        if (config('session.drive') == 'redis') {
            $this->drive = new Redis();
        } else {
            $this->drive = new File();
        }

        if ($response) {
            $response->cookie($this->name, $this->session_id, time() + $this->time, '/');
        }

        $this->data = $this->drive->get($this->prefix . $this->session_id);
    }

    public function getId()
    {
        return $this->session_id;
    }

    public function set($key, $val)
    {
        $this->data[$key] = $val;
    }

    public function get($key = null)
    {
        if ($key) {
            return array_get($this->data, $key);
        } else {
            return $this->data;
        }
    }

    public function del($key)
    {
        unset($this->data[$key]);
    }


    public function __destruct()
    {
        if ($this->session_id) {
            $this->drive->set($this->prefix . $this->session_id, $this->data, $this->time);
        }
    }
}