<?php 
	class WriteData extends SuperBase
	{
		public function registerUnverifiedUser($data){
			$this->db->query("INSERT INTO upss SET email=?s, pass=?s, verified=0, isMusican=?i, validationHash=?s", $data['email'], $data['pass'], $data['isMusican'], $data['validateHash']);
		}

		public function validateUser($id){
			$this->db->query("UPDATE upss SET validationHash=null, verified=1 WHERE id = ?i", $id);
		}

		public function writeUser($data){
			//mapping 
			$vk_id 			= $data['vk_id'];
			$fb_id 			= $data['fb_id'];
			$ok_id 			= $data['ok_id'];
			$bdate 			= $data['bdate'];
			$f_name 		= $data['f_name'];
			$l_name 		= $data['l_name'];
			$sex 			= $data['sex'];
			$avatar 		= $data['avatar'];
			$importedFrom 	= $data['importedFrom'];

			$result = $this->db->query("INSERT INTO users (vk_id, fb_id, ok_id, bdate, firstname, lastname, sex, avatar, importedFrom) VALUES (?i, ?i, ?i, ?s, ?s, ?s, ?i, ?s, ?i)", $vk_id, $fb_id, $ok_id, $bdate, $f_name, $l_name, $sex, $avatar, $importedFrom);
			return $result;
		}

		public function setSessionToUserById($id, $session){
			$this->db->query("UPDATE users SET session=?s WHERE id = ?i", $session, $id);
		}

		public function enableVideoForUser($videoId, $userId){
			$allowedVideos = $this->db->getRow('SELECT allowedVideos FROM users WHERE id = ?i', $userId);
			$allowedVideos = $allowedVideos['allowedVideos'].'|'.$videoId;
			return $this->db->query("UPDATE users SET allowedVideos=?s WHERE id = ?i", $allowedVideos, $userId);
		}

		public function writeTransaction($data){
			$rrn 							= $data['rrn'];
			$masked_card 					= $data['masked_card'];
			$sender_cell_phone 				= $data['sender_cell_phone'];
			$response_signature_string 		= $data['response_signature_string'];
			$response_status 				= $data['response_status'];
			$sender_account 				= $data['sender_account'];
			$fee 							= $data['fee'];
			$rectoken_lifetime 				= $data['rectoken_lifetime'];
			$reversal_amount 				= $data['reversal_amount'];
			$settlement_amount 				= $data['settlement_amount'];
			$actual_amount 					= $data['actual_amount'];
			$order_status 					= $data['order_status'];
			$response_description 			= $data['response_description'];
			$verification_status 			= $data['verification_status'];
			$order_time 					= $data['order_time'];
			$actual_currency 				= $data['actual_currency'];
			$order_id 						= $data['order_id'];
			$parent_order_id 				= $data['parent_order_id'];
			$merchant_data 					= $data['merchant_data'];
			$tran_type 						= $data['tran_type'];
			$eci 							= $data['eci'];
			$settlement_date 				= $data['settlement_date'];
			$payment_system 				= $data['payment_system'];
			$rectoken 						= $data['rectoken'];
			$approval_code 					= $data['approval_code'];
			$merchant_id 					= $data['merchant_id'];
			$settlement_currency 			= $data['settlement_currency'];
			$payment_id 					= $data['payment_id'];
			$product_id 					= $data['product_id'];
			$currency 						= $data['currency'];
			$card_bin 						= $data['card_bin'];
			$response_code 					= $data['response_code'];
			$card_type 						= $data['card_type'];
			$amount 						= $data['amount'];
			$sender_email 					= $data['sender_email'];
			$signature 						= $data['signature'];
			$buyer_id						= $data['buyer_id'];

			$result = $this->db->query("INSERT INTO transactions (order_id, order_desc, amount, currency, order_status, signature, tran_type, sender_cell_phone, sender_account, masked_card, card_bin, card_type, rrn, approval_code, response_code, response_description, reversal_amount, settlement_amount, settlement_currency, order_time, settlement_date, eci, fee, payment_system, sender_email, payment_id, actual_amount, actual_currency, product_id, merchant_data, verification_status, rectoken, rectoken_lifetime, buyer_id) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?i)", $order_id, '', $amount, $currency, $order_status, $signature, $tran_type, $sender_cell_phone, $sender_account, $masked_card, $card_bin, $card_type, $rrn, $approval_code, $response_code, $response_description, $reversal_amount, $settlement_amount, $settlement_currency, $order_time, $settlement_date, $eci, $fee, $payment_system, $sender_email, $payment_id, $actual_amount, $actual_currency, $product_id, $merchant_data, $verification_status, $rectoken, $rectoken_lifetime, $buyer_id);
			return $result;
		}
	}
?>