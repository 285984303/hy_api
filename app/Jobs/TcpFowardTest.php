<?php

class Client
{
    private $client;
    private $channel = 0;
    private $online_list;

    public function __construct()
    {
        $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $this->client->on('Connect', array($this, 'onConnect'));
        $this->client->on('Receive', array($this, 'onReceive'));
        $this->client->on('Close', array($this, 'onClose'));
        $this->client->on('Error', array($this, 'onError'));
    }

    public function connect()
    {
        $fp = $this->client->connect("202.98.194.238", 6488, 1);
        if (!$fp) {
            echo "Error: {$fp->errMsg}[{$fp->errCode}]\n";
            return;
        }
    }

    public function onReceive($cli, $data)
    {

        echo bin2hex($data);

    }

    public function onConnect($cli)
    {
        fwrite(STDOUT, "Enter your msg: ");
        $msg = trim(fgets(STDIN));

        $data = hex2bin($msg);
        $cli->send($data . "\n");
        swoole_event_add(STDIN, function ($fp) {
            global $cli;
            $msg = trim(fgets(STDIN));
            $data = hex2bin($msg);

            $cli->send($data . "\n");
        });
    }

    public function onClose($cli)
    {
        echo "Client close connection\n";
    }

    public function onError()
    {
    }

    public function send($data)
    {
        $this->client->send($data);
    }

    public function isConnected()
    {
        return $this->client->isConnected();
    }
}

$cli = new Client();
$cli->connect();