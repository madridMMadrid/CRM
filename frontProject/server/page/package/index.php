<?php
	class package extends API{
		public function __construct(){
			parent::__construct();

			if(count($this->url) > 1){
				switch ($this->url[1]) {
				    case 'list':
				        echo json_encode($this->pd->package_list());
				        break;

				    case 'search':
				    	echo json_encode($this->pd->package_search($_POST['string']));
				    	break;
				}
			}
		}
	}
?>
