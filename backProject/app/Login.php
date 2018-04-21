<?php

namespace App;

use \Core\APIUnauthorized;
use \Firebase\JWT\JWT;
use Safety\SaferCrypto;

/**
 * Class login
 */
class Login extends APIUnauthorized
{

    /**
     * Login constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Log in.
     *
     * @throws ?
     * @return array
     */
    public function login()
    {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $login = $this->pd->login($username, $password)[0];

        if ($login['id']) {
            $tokenId = base64_encode(random_bytes(32));
            $issuedAt = time();
            $notBefore = $issuedAt;             //Adding 10 seconds
            $expire = ($notBefore + 60) * 60;            // Adding 60 seconds
            $serverName = gethostname(); // Retrieve the server name from config file

            /*
            * Create the token as an array
            */
            $data = [
                'iat' => $issuedAt,         // Issued at: time when the token was generated
                'jti' => $tokenId,          // Json Token Id: an unique identifier for the token
                'iss' => $serverName,       // Issuer
                'nbf' => $notBefore,        // Not before
                'exp' => $expire,           // Expire
                'data' => [                  // Data related to the signer user
                    'employeeId' => $login['id'], // userid from the users table
                    'employeeName' => $login['username'],
                    'employeeType' => $login['type']
                ]
            ];

            /*
            * Extract the key, which is coming from the config file.
            *
            * Best suggestion is the key to be a binary string and
            * store it in encoded in a config file.
            *
            * Can be generated with base64_encode(openssl_random_pseudo_bytes(64));
            *
            * keep it secure! You'll need the exact key to verify the
            * token later.
            */
            $secretKey = base64_decode($this->settings->jwtKey);

            /*
             * Encode the array to a JWT string.
             * Second parameter is the key to encode the token.
             *
             * The output string can be validated at http://jwt.io/
             */
            $jwt = JWT::encode(
                $data,        //Data to be encoded in the JWT
                $secretKey,   // The signing key
                'HS512'  // Algorithm used to sign the token
            );

            $message = $_SERVER['REMOTE_ADDR'];
            $key = hex2bin('000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f');

            $crypt = SaferCrypto::encrypt($message, $key);

            $jwtToken = base64_encode($crypt) . '|\|' . $jwt;

            // Get the employee
            $employee = $this->pd->getEmployee($data['data']['employeeId']);

            // Employee mapping
            $employee['employeeId'] = (int)$employee['employeeId'];
            $employee['type'] = (int)$employee['type'];

            // Return the result.
            return array(
                'jwt' => $jwtToken,
                'user' => $employee
            );
        } else {
            return array('success' => false);
        }
    }
}
