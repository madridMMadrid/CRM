<?php

namespace App;

use Core\APIUnauthorized;
use DateTime;
use \ZMQContext;
use \ZMQ;

/**
 * Class clients
 */
class Agent extends APIUnauthorized
{
    // MAIN

    /**
     * Client constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function pushSocket($entryData)
    {
        // This is our new stuff
        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://localhost:5555");

        $socket->send(json_encode($entryData));
    }

    public function register($name, $email, $phone, $password, $profession, $picture)
    {
        //
        $inviteHash = $this->shortUrl->generateShortUrl();
        // зарегистрировать агента
        $this->pd->registerAgent($name, $email, $phone, $password, $profession, $picture, $inviteHash);
    }

    public function login(string $phone, string $password): array
    {
        // вход для агентов
        $agent = $this->pd->loginAgent($phone, $password)[0];
        $actionHistory = $this->pd->getActionHistory((int)$agent['id']);
        $specials = $this->pd->getSpecials();

        if (!$agent['id']) {
            return [
              'success' => false,
              'error' => 'Agent not found'
            ];
        } else {
            $agentData = [
              'id' => $agent['id'],
              'name' => $agent['name'],
              'profession' => $agent['profession'],
              'picture' => $agent['picture'],
              'balance' => $agent['balance'],
              'inviteHash' => $agent['inviteHash']
            ];

            return [
              'success' => true,
              'data' => [
                'agent' => $agentData,
                'actionHistory' => $actionHistory,
                'specials' => $specials
              ],
            ];
        }
    }
}
