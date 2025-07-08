<?php

require 'vendor/autoload.php';

use Predis\Client as Redis;

class SocketServer
{
    private \Socket $server;
    private array $clients = [];

    public function __construct(

        private Redis   $redis,
        private string  $host = "127.0.0.1",
        private int     $port = 8080)
    {

        $this->clients = [ $this->server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) ];

        socket_set_option($this->server, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->server, $this->host, $this->port);
        socket_listen($this->server);

        echo "WebSocket server running on ws://$this->host:$this->port\n";

        $this->runBackgroundListener();
        $this->listenForIncomingMessages();
    }

    private function runBackgroundListener(): void
    {
        if (pcntl_fork() !== 0) return;

        ($pubsub = $this->redis->pubSubLoop())->subscribe('broadcast');

        foreach ($pubsub as $message)
        {
            if ($message->kind !== 'message') continue;
            file_put_contents('/tmp/ws-redis-msg.txt', $message->payload);
        }

        exit;
    }

    private function listenForIncomingMessages(): void
    {
        while (true) {

            $read   = $this->clients;
            $write  = $except = [];

            socket_select($read, $write, $except, 0);

            foreach ($read as $sock) {

                if ($sock === $this->server) {

                    $this->clients[] = socket_accept($this->server);
                    continue;
                }

                if (@socket_recv($sock, $buffer, 2048, 0) < 1) {

                    unset($this->clients[array_search($sock, $this->clients)]);
                    socket_close($sock);

                    continue;
                }

                if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $buffer, $matches)) {

                    $key = base64_encode(
                        pack('H*', sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11'))
                    );

                    $headers = "HTTP/1.1 101 Switching Protocols\r\n" .
                        "Upgrade: websocket\r\n" .
                        "Connection: Upgrade\r\n" .
                        "Sec-WebSocket-Accept: $key\r\n\r\n";

                    socket_write($sock, $headers);
                    continue;
                }

                echo "From client: {$this->decode($buffer)}\n";
            }

            $this->checkForRedisMessages();
            usleep(100000);
        }
    }

    private function checkForRedisMessages(): void
    {
        if (file_exists($file = '/tmp/ws-redis-msg.txt')
                && ($msg = trim(file_get_contents($file))))
        {

            echo "From Redis: $msg\n";

            foreach ($this->clients as $client)
            {
                if ($client !== $this->server)
                    @socket_write($client, $this->encode($msg));
            }

            file_put_contents($file, '');
        }
    }

    private function encode(string $text): string
    {
        $b1  = 0x81;
        $len = strlen($text);

        return match (true) {

            $len < 126   => pack('CC',   $b1, $len),
            $len < 65536 => pack('CCn',  $b1, 126, $len),

            default => pack('CCNN', $b1, 127, 0, $len)
        } . $text;
    }

    private function decode(string $data): string
    {
        $length = ord($data[1]) & 127;

        if ($length == 126) {
            $masks  = substr($data, 4, 4);
            $data   = substr($data, 8);
        }

        elseif ($length == 127) {
            $masks  = substr($data, 10, 4);
            $data   = substr($data, 14);
        }

        else {
            $masks  = substr($data, 2, 4);
            $data   = substr($data, 6);
        }

        $text = '';

        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }

        return $text;
    }
}

new SocketServer(new Redis(), port: 8081);
