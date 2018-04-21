<?php 
	class Settings {
		public $root;
		public $httpRoot;
		public $path = array(
			"parts" 	=> "/system/frontend/markup/part/",
			"pages"		=> "/system/frontend/markup/page/",
			"plugins"	=> "/system/frontend/js/plugins/"
		);

		public $jwtKey = 'tsss,itisasecretsuperspysecret';

		public function __construct(){
			$this->root = $_SERVER['DOCUMENT_ROOT'];
			$this->httpRoot = "http://".$_SERVER['HTTP_HOST']."/system/";
		}
	}
?>