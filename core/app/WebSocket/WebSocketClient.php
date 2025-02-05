<?php

namespace App\WebSocket;

use Ratchet\Client\WebSocket;
use Ratchet\Client\Connector;
use React\EventLoop\Factory;
use React\Socket\Connector as ReactConnector;
use Illuminate\Support\Facades\Cache;
class WebSocketClient
{
    protected $apiAccessToken;
    protected $sportType;

    public function __construct($apiAccessToken, $sportType)
    {
        $this->apiAccessToken = $apiAccessToken;
        $this->sportType = $sportType;
    }

    public function connect()
    {
        return true;
        // $loop = Factory::create();
        // $connector = new Connector($loop, new ReactConnector($loop, [
        //     'dns' => '8.8.8.8',
        //     'timeout' => 10,
        // ]));

        // $url = sprintf(
        //     'ws://85.217.222.218:8765/ws/%s?tkn=%s',
        //     $this->sportType,
        //     $this->apiAccessToken
        // );

        // $connector($url)->then(function (WebSocket $conn) {
        //     $conn->on('message', function ($msg) {
        //         $cacheKey='websocket_data'.$this->sportType;
        //         Cache::put($cacheKey, (string)$msg, 5);
        //         // echo "Received: {$msg}\n";
        //         // Handle the message (e.g., store it in the database)
        //     });

        //     $conn->on('close', function ($code = null, $reason = null) {
        //         echo "Connection closed ({$code} - {$reason})\n";
        //     });
        // }, function ($e) use ($loop) {
        //     echo "Could not connect: {$e->getMessage()}\n";
        //     $loop->stop();
        // });

        // $loop->run();
    }
}
