<?php 
	class User extends SystemClass{
		public $self = null;

		function __construct(){
			// parent::__construct();
			
			// if(session_id() == '') {
			//     session_start();
			// }
		}

		private function checkIsLogged(){
			
		}

		public function logout(){
			session_unset();
			$this->wd->setSessionToUserById($id, '');
		}

		public function login($username, $password){
			return $this->pd->login($username, $password);
		}

		public function isLogged(){
			return isset($_SESSION['userId']) === true;
		}

		public function getUserId(){
			return $_SESSION['userId'];
		}

		public function getUserData(){
			return $this->pd->getUserById($this->getUserId());
		}

		public function connectAcountToSessionById($id){
			$this->wd->setSessionToUserById($id, session_id());
			$_SESSION['userId'] = $id;
		}
	}
?>