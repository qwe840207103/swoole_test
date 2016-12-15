<?php
class Server
{
    private $serv;

    public function __construct()
    {
        $this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->set(array('worker_num' => 8, 'daemonize' => false,));
        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('Workerstart', array($this, 'onWorkerstart'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Close', array($this, 'onClose'));
        $this->serv->start();
    }

    public function onStart($serv)
    {
        echo "Start\n";
    }

    public function onWorkerstart(swoole_server $serv,  $fd)
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    public function onConnect($serv, $fd, $from_id)
    {
        $serv->send($fd, "set name:{$fd}!");
    }

    public function onReceive(swoole_server $serv, $fd, $from_id, $data)
    {
        $name = trim($this->redis->get($fd));
        if (!$name) {
            $this->redis->set($fd, $data);
        } else {
            foreach ($serv->connections as $value) {
                if ($fd != $value) {
                    $serv->send($value, $name . ':' . "$data");
                }
            }
        }
        $data = trim($data);
        if($data=='quit'){
            $serv->stop();
            $serv->shutdown();
        }
    }

    public function onClose($serv, $fd, $from_id)
    {
        $this->redis->del($fd);
        echo "Client {$fd} close connection\n";
    }
}
$server = new Server();