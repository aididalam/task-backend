<?php

require __DIR__ . '/vendor/autoload.php';

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use App\WebSockets\TaskWebSocket;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new TaskWebSocket()
        )
    ),
    8331 // WebSocket port
);

echo "WebSocket server running on ws://localhost:8331\n";
$server->run();
