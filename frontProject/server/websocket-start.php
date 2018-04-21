<?php

require __DIR__ . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

    require_once 'websocket.php';

    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new WebSocket()
            )
        ),
        8080
    );

    $server->run();