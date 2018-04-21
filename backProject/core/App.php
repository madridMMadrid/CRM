<?php

namespace Core;

use Core\Config\Settings;
use Doctrine\Common\Inflector\Inflector;
use Safety\SaferCrypto;
use Exception;
use Firebase\JWT\JWT;

/**
 * Main class of the Application.
 *
 * @package Core
 */
class App
{
    /**
     * Check the token.
     *
     * @return bool|object|string
     */
    public static function token()
    {
        $settings = new Settings();

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $message = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $message = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $message = $_SERVER['REMOTE_ADDR'];
        }

        $key = hex2bin('000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f');

        $jwt = self::bearer();

        $arr = explode("|\|", $jwt, 2);
        try {
            $crypt = SaferCrypto::encrypt($message, $key);
            $crypt = SaferCrypto::decrypt(base64_decode($arr[0]), $key);
        } catch (Exception $e) {
            // $e->getMessage();
            return false;
        }

        if ($message != $crypt || !$jwt) {
            return false;
        } else {
            $jwt = $arr[1];
            if ($jwt) {
                try {
                    $secretKey = base64_decode($settings->jwtKey);
                    $token = JWT::decode($jwt, $secretKey, array('HS512'));
                    return $token;
                } catch (Exception $e) {
                    $e->getMessage();
                }
            }

            return false;
        }
    }

    /**
     * Get bearer token.
     */
    public static function bearer()
    {
        $headers = apache_request_headers();
        $headers = $headers['X-Authorization'];

        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
    }

    /**
     * Parse url.
     *
     * @return array|string
     */
    public static function url()
    {
        if (count($_GET) > 0 && $_GET['url']) {
            $url = $_GET['url'];
            $url = rtrim($url, '/');
            $url = explode('/', $url);
            if (!$url[0]) {
                $url[0] = 'index';
            }
        } else {
            $url = array('index');
        }
        return $url;
    }

    /**
     * Configuration.
     */
    public function configure()
    {
    }

    /**
     * Navigate to the required page.
     */
    public function run()
    {
        $url = $this->url();

        if ($url[0] != 'storage') {
            $file = $url[0];
            $file = Inflector::singularize($file);
            $file = ucfirst($file);
            $class = '\App\\' . $file;
            if (require_once "../app/{$file}.php") {
                new $class;
            } else {
                echo '404';
            }
        } else {
            $settings = new Settings();
            echo file_get_contents($settings->root . '/' . $_GET['url']);
        }
    }
}
