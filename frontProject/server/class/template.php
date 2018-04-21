<?php 
	class Template {
		private $settings;

		function __construct(){
			$this->settings = new settings();
		}

		public function getTemplate($path, $templateData){
			$templateData['rootPath'] 	= $this->settings->httpRoot;
			$templateClass				= $this;
			return $this->loadTemplate($this->settings->root.$this->settings->path['pages'].$path, $templateData, $templateClass);
		}

		public function getPart($path, $templateData = null){
			return $this->loadTemplate($this->settings->root.$this->settings->path['parts'].$path, $templateData);
		}

		public function buildPage($path, $templateData = null){
			echo $this->getPart('head.html'); 
			echo $this->getTemplate($path, $templateData);
			echo $this->getPart('foot.html');
		}

		private function loadTemplate($path, $templateData, $templateClass = null){
			ob_start();
			include($path);
			$template = ob_get_contents(); 
		    ob_end_clean();
		    return $template;
		}
	}
?>