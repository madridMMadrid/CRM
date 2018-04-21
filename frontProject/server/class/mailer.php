<?php 
	class Mailer {
		public function send($data){
			$to      = $data['reciever'];
			$subject = $data['subject'];
			$message = $data['content'];

			$headers = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: system@soundgeek.octomart.ru' . "\r\n" .
			    'Reply-To: system@soundgeek.octomart.ru' . "\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			$result = mail($to, $subject, $message, $headers);
			return $result;
		}
	}
?>