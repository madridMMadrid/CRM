<?php 
use \Firebase\JWT\JWT;

class APIunlogged {
	protected $pd;
	protected $settings;
	protected $url;

	public function __construct(){
		header('Content-Type: application/json');

		$this->pd 			= new ParseData();
		$this->settings 	= new Settings();
		$this->url 			= urlParts();
	}
}

class API extends APIunlogged{
	protected $user;

	public function __construct(){
		parent::__construct();

		$this->user = checkAuthToken();
		if($this->user){
			$this->user = get_object_vars($this->user);
			$this->user = get_object_vars($this->user['data']);
		} else {
			errorStack();
		}
	}
}

function errorStack(){
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(array('success' => false, 'type' => 'employee is not verified, try relogin', 'action' => 'relogin'));
	die();
}

function checkAuthToken()
{
	$settings = new settings();

	function getBearerToken() {
		$headers = apache_request_headers();
		$headers = $headers['X-Authorization'];
		// HEADER: Get the access token from the header
		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
	}

	$message = $_SERVER['REMOTE_ADDR'];
	$key = hex2bin('000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f');

	$jwt = getBearerToken();

	// playground
	$arr = explode("|\|", $jwt, 2);
	try {
        $crypt = SaferCrypto::encrypt($message, $key);
	    $crypt = SaferCrypto::decrypt(base64_decode($arr[0]), $key);
    }
    catch(Exception $e) {
    	errorStack();
    }

	if($message != $crypt || !$jwt){
	    errorStack();
	}

	$jwt = $arr[1];
	if ($jwt) {
        try {
            $secretKey = base64_decode($settings->jwtKey);
            $token = JWT::decode($jwt, $secretKey, array('HS512'));
    		return $token;
        } catch (Exception $e) {
             // * the token was not able to be decoded.
             // * this is likely because the signature was not able to be verified (tampered token)
        	errorStack();
        }
    }
}
?>