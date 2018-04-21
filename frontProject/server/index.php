<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Authorization');

ini_set("display_errors", 1);
ini_set("track_errors", 1);
ini_set("html_errors", 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Moscow');

// get composer included vendors
require __DIR__ . '/vendor/autoload.php';

require_once 'class/dbconnect.php';
require_once 'class/parseData.php';
require_once 'class/settings.php';
require_once 'class/crypto.php';
require_once 'class/API.php';

class SuperBase {
	public $db;

	function __construct(){
		$this->db = new SafeMySQL();
	}
}

// trait Singleton {
//     static private $instance = null;

//     private function __construct() { /* ... @return Singleton */ }  // Защищаем от создания через new Singleton
//     private function __clone() { /* ... @return Singleton */ }  // Защищаем от создания через клонирование
//     private function __wakeup() { /* ... @return Singleton */ }  // Защищаем от создания через unserialize

//     static public function getInstance() {
// 		return 
// 		self::$instance===null
// 			? self::$instance = new static()//new self()
// 			: self::$instance;
//     }
// }

function urlParts(){
	if(count($_GET) > 0 && $_GET['url']){
		$url = $_GET['url'];
		$url = rtrim($url, '/');
		$url = explode('/', $url);
		if(!$url[0]){
			$url[0] = 'index';
		}
	} else {	
		$url = array('index');
	}
	return $url;
}

callController();

// navigate to required page
function callController(){
	$url = urlParts();

	if($url[0] != 'storage'){
		if(include('page/'.$url[0].'/index.php')){
			$controller = new $url[0];
		} else {
			echo "404";
		}
	} else {
		$settings = new Settings();
		echo file_get_contents($settings->root."/".$_GET['url']);
	}
}

?>