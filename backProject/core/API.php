<?php

namespace Core;

use Exception;
use Safety\SaferCrypto;
use \Firebase\JWT\JWT;

/**
 * Class API.
 */
class API extends APIUnauthorized
{
    /**
     * API constructor.
     */
    public function __construct()
    {
        $this->user = App::token();

        // print_r('token'.$this->user);

        if ($this->user) {
            $this->user = get_object_vars($this->user);
            $this->user = get_object_vars($this->user['data']);
        } else {
            echo json_encode(array('error' => 'Auth error'), true);
            die();
        }

        parent::__construct();
    }
}
