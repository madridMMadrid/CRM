<?php

namespace App;

use \Core\APIUnauthorized;
use \ZMQContext;
use \ZMQ;

/**
 * Class Request
 *
 * @package App
 */
class Request extends APIUnauthorized
{
    /**
     * Request constructor.
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

    /**
     * Add a request.
     *
     * @param  string $name
     * @param  string $phone
     * @param  string $email
     * @param  string $question
     * @param  int    $packageId
     * @param  int    $priceId
     * @param  int    $city
     * @return boolean
     */
    public function addRequest($name, $phone, $email, $question, $packageId, $priceId, $city)
    {
        // Get the user by the phone.
        $client = $this->pd->getClientByPhone($phone);

        // Check if the user already exists.
        if (!$client) {
            $this->pd->createClient($name, $phone, $email, $city);
            $client = $this->pd->getClientByPhone($phone);
        }

        // Attach a package to the client.
        if ($packageId && $priceId) {
            $this->pd->clientAddPackage($client['id'], $packageId, $priceId, 0);
        }

        $entryData = array(
            'category' => 'newRequests'
        );

        $this->pushSocket($entryData);

        // Add a request
        return (bool)$this->pd->addRequest($client['id'], $question, $packageId);
    }
}
