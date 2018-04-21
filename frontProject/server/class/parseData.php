<?php 
	class ParseData extends SuperBase
	{
		public function login($username, $password){
			return $this->db->getRow('SELECT * FROM employees WHERE username = ?s AND password = ?s', $username, $password);
		}

		// salesmen procedures start
		public function newClients(){
			return $this->db->getAll('SELECT * FROM clients WHERE isNew = 1');
		}

		public function requiresAttentionClients(){
			return $this->db->getAll('SELECT * FROM clients WHERE requiresAttention = 1');
		}

		public function clientList($from, $to){
			return $this->db->getAll('SELECT id,name,email,phone,isFrozen,requiresAttention,isNew,isDebtHold FROM clients LIMIT '.$from.', '.$to);
		}

		public function clientSearch($from, $to, $string){
			return $this->db->getAll('SELECT id,name,email,phone,isFrozen,requiresAttention,isNew,isDebtHold FROM clients WHERE concat(name, " ", phone, " ", email) like "%'.$string.'%" LIMIT ?i, ?i', $from, $to);
		}

		public function getClientById($id){
			return $this->db->getRow('SELECT 
				c.id, 
				c.name, 
				c.phone, 
				c.email, 
				c.gender,
				c.debt, 
				c.adressesId,
				c.timesId,
				c.isFrozen,
				c.isNew,
				c.requiresAttention,
				c.isDebtHold
				FROM clients c 
				WHERE c.id = ?i', $id);
		}

		public function clients_getCommentsById($id, $from, $to){
			return $this->db->getAll('SELECT 
				cc.id,
				cc.employeeId,
				cc.comment,
				cc.date
				FROM client_comments cc
				WHERE cc.clientId = ?i
				ORDER BY cc.date DESC LIMIT ?i, ?i', $id, $from, $to);
		}

		public function clients_pushCommentById($employeeId, $clientId, $comment){
			return $this->db->query("
				INSERT INTO client_comments (
					clientId,
					employeeId, 
					comment
				) VALUES (?i, ?i, ?s)", $clientId, $employeeId, $comment);
		}
				
		public function getClientPackagesById($id){
			return $this->db->getAll('SELECT 
				cp.id,
				cp.packageId,
				cp.priceId,
				cp.isHold,
				cp.comment,
				cp.balance,
	
				p.id 		AS packageId,
				p.name 		AS packageName,

				pp.packageLength,
				pp.price
				
				FROM client_packages cp 
				LEFT JOIN package p 						ON cp.packageId 	= p.id
				LEFT JOIN package_prices pp 				ON cp.priceId 		= pp.id
				WHERE cp.clientId = ?i ORDER BY packageLength ASC', $id);
		}

		public function clients_changePackage($oldPackageId, $newPackageId, $newPriceId, $amount, $paymentType, $blockIt){
			$blockIt = $blockIt ? 1 : 0;

			return $this->db->query("
				UPDATE client_packages 
				SET packageId = ?i, priceId = ?i, isHold = ?i WHERE id = ?i", 
					$newPackageId, $newPriceId, $blockIt, $oldPackageId);
		}

		public function clients_changePackageComment($packageId, $comment){
			return $this->db->query("UPDATE client_packages SET comment = ?s WHERE id = ?i", $comment, $packageId);
		}

		public function clients_foreshowPackage($id, $clientPackageId, $newPackageId){
			return $this->db->getRow('SELECT 
				cp.balance 						AS balance,
				newPackage.name 				AS newPackageName,
				oldPackage.name 				AS oldPackageName,
				oldPackage.id 					AS oldPackageId,
				oldPackagePrice.id 				AS packagePriceId,
				oldPackagePrice.price 			AS oldPackagePrice,
				oldPackagePrice.packageLength 	AS oldPackageLength

				FROM package newPackage
				LEFT JOIN client_packages 	clientPackages 		ON clientPackages.clientId = ?i
				LEFT JOIN client_packages 	cp 					ON cp.id = ?i
				LEFT JOIN package 			oldPackage 			ON oldPackage.id = cp.packageid
				LEFT JOIN package_prices 	oldPackagePrice 	ON cp.priceId = oldPackagePrice.id
				WHERE newPackage.id = ?i', $id, $clientPackageId, $newPackageId);
		}

		public function clients_getDebtById($clientId){
			return $this->db->getRow('SELECT debt FROM clients WHERE id = ?i', $clientId);
		}

		public function clients_setDebtHold($clientId, $debtState){
			return $this->db->query("UPDATE clients SET isDebtHold = ".$debtState." WHERE id = ?i", $clientId);
		}

		public function clients_getDeliveryByClientId($clientId, $dateFrom, $dateTo){
			return $this->db->getAll('SELECT id, packageId, state, date FROM client_delivery WHERE clientId = ?i and Date between ?s and ?s ORDER BY date ASC', $clientId, $dateFrom, $dateTo);
		}

		public function clients_setDeliveryByClientId($clientId, $packageId, $period, $state, $employeeId){
			// clean up data in case of primary SQL
			$clientId 	= intval($clientId);
			$packageId 	= intval($packageId);
			$state 		= intval($state);
			$employeeId = intval($employeeId);

			$SQL = 'INSERT INTO client_delivery (clientId, packageId, state, date, employeeId) VALUES';

			foreach($period as $dt){
				// loop between dates
				$SQL .= "(".$clientId.", ".$packageId.", ".$state.", '".$dt->format('Y-m-d H:i:s')."', ".$employeeId."),";
			}

			return $this->db->query(rtrim($SQL,", "));
		}

		public function clients_prolongatePackage($packageId, $state){
			return $this->db->query("UPDATE client_packages SET isHold = ".$state." WHERE id = ?i", $packageId); 
		}

		public function clients_setProlongationHold($packageId, $state){
			return $this->db->query("UPDATE client_packages SET isHold = ".$state." WHERE id = ?i", $packageId);
		}

		// salesmen procedures end

		// history procedures start
		public function history_changePackage($employeeId, $oldPackageId, $packageId, $amount, $paymentType, $requiresPayment){
			$requiresPayment = $requiresPayment ? 1 : 0;
			return $this->db->query("INSERT INTO 
				client_packChanges (oldPackageId, employeeId, packageId, amount, paymentType, requiresPayment) VALUES (?i, ?i, ?i, ?i, ?i, ?i)",
									$oldPackageId, $employeeId, $packageId, $amount, $paymentType, $requiresPayment);
		}

		public function history_payDebt($employeeId, $clientId, $amount, $paymentType){
			return $this->db->query("INSERT INTO 
				client_payDebt (clientId, employeeId, amount, paymentType) VALUES (?i, ?i, ?i, ?i)", 
				$clientId, $employeeId, $amount, $paymentType);
		}

		public function history_cancelDebt($id){
			return $this->db->query("DELETE FROM
				client_payDebt WHERE id = ?i AND isConfirmed = 0", 
				$id);
		}

		public function history_prolongation($employeeId, $clientId, $amount, $paymentType){
			return $this->db->query("INSERT INTO 
				client_prolongation (clientId, employeeId, amount, paymentType) VALUES (?i, ?i, ?i, ?i)", 
				$clientId, $employeeId, $amount, $paymentType);
		}

		public function history_deleteProlongation($id){
			return $this->db->query("DELETE FROM
				client_prolongation WHERE id = ?i AND isConfirmed = 0", 
				$id);
		}
		// history procedures end

		// package procedures start
		public function package_list(){
			return $this->db->getAll('SELECT id,name FROM package');
		}

		public function package_getById($id){
			return $this->db->getRow('SELECT name,deliveryDaysPeriod FROM package WHERE id = ?i', $id);
		}

		public function package_pricesGetByPackageId($packageId){
			return $this->db->getAll('SELECT id, packageLength, price FROM package_prices WHERE packageId = ?i', $packageId);
		}

		public function package_getPriceByPackageId($packageId){
			return $this->db->getRow('SELECT id, packageLength, price FROM package_prices WHERE packageId = ?i', $packageId);
		}

		public function package_search($string){
			return $this->db->getAll('SELECT id,name,deliveryDaysPeriod FROM package WHERE concat(name) like "%'.$string.'%"');
		}
		// package procedures end

		public function getTableLen($dbname){
			$query = $this->db->getRow('SELECT COUNT(*) FROM '.$dbname);
			return $query['COUNT(*)'];
		}
	}
?>