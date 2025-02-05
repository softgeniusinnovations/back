<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\WebSocket\WebSocketClient;

class WebSocketConnect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:connect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Connect to WebSocket and get data from third-party API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $apiAccessToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1biI6InRyYW1iZXQiLCJuYmYiOjE3MjA2Mzk0OTYsImV4cCI6MTcyMDY0MzA5NiwiaWF0IjoxNzIwNjM5NDk2fQ.vXJXmF-JdMkx90JC3EwJdOXgdnI88TDcD2hdy7ORc34';
        $sportType = 'soccer';

        $client = new WebSocketClient($apiAccessToken, $sportType);
        $client->connect();

        return 0;
    }
}
