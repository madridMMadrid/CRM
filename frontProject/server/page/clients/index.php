<?php
	class clients extends API {
		public function __construct(){
			parent::__construct();

			if(count($this->url) > 1){
				switch ($this->url[1]) {
				    case 'newClients':
				        echo json_encode($this->pd->newClients());
				        break;

				   case 'requiresAttentionClients':
				   		echo json_encode($this->pd->requiresAttentionClients());
				   		break;

				   	case 'clientList':
				   		echo json_encode($this->pd->clientList($_POST['from'], $_POST['to']));
				   		break;

				   	case 'clientSearch':
				   		echo json_encode($this->pd->clientSearch($_POST['from'], $_POST['to'], $_POST['string']));
				   		break;

				   	case 'getClientById':
				   		echo json_encode($this->getClientById($_POST['id']));
				   		break;

				   	case 'foreshowChangePackage':
				   		echo json_encode(
				   			$this->foreshowChangePackage(
				   				$_POST['clientId'], 
				   				$_POST['clientPackageId'],
				   				$_POST['newPackageId']
				   			)
				   		);
				   		break;

				   	case 'changePackage':
				   		echo json_encode(
				   			$this->changePackage(
				   				$this->user['employeeId'],
				   				$_POST['oldPackageId'],
				   				$_POST['newPackageId'],
				   				$_POST['newPriceId'],
				   				$_POST['amount'],
				   				$_POST['paymentType']
				   			)
				   		);
				   		break;

				   	case 'changePackageComment':
				   		echo json_encode($this->pd->clients_changePackageComment($_POST['id'], $_POST['comment']));
				   		break;

				   	case 'payDebt': 
				   		echo json_encode(
				   			$this->payDebt(
				   				$this->user['employeeId'], 
				   				$_POST['clientId'], 
				   				$_POST['amount'], 
				   				$_POST['paymentType']
				   			)
				   		);
				   		break;

				   	case 'cancelPayDebt':
				   		echo json_encode(
				   			$this->cancelPayDebt(
				   				$_POST['id'],
				   				$_POST['clientId']
				   			)
				   		);
				   		break;

				   	case 'pushComment': 
				   		echo json_encode(
				   			$this->pushComment(
				   				$this->user['employeeId'], 
				   				$_POST['clientId'], 
				   				$_POST['comment']
				   			)
				   		);
				   		break;

				   	case 'getClientCommentsById': 
				   		echo json_encode(
				   			$this->getClientCommentsById(
				   				$_POST['clientId'], 
				   				$_POST['from'], 
				   				$_POST['to']
				   			)
				   		);
				   		break;

				   	case 'getDelivery': 
				   		echo json_encode(
				   			$this->getClientDelivery(
				   				$_POST['clientId']
				   			)
				   		);
				   		break;

				   	case 'setDelivery': 
				   		echo json_encode(
				   			$this->setClientDelivery(
				   				$_POST['clientId'],
				   				$_POST['packageId'],
				   				$_POST['from'],
				   				$_POST['to'],
				   				$_POST['state']
				   			)
				   		);
				   		break;

				   	case 'prolongation': 
				   		echo json_encode(
				   			$this->prolongation(
				   				$this->user['employeeId'], 
				   				$_POST['clientId'], 
				   				$_POST['packageId'], 
				   				$_POST['amount'], 
				   				$_POST['paymentType']
				   			)
				   		);
				   		break;

				   	case 'deleteProlongation':
				   		echo json_encode(
				   			$this->cancelProlongation($_POST['id'], $_POST['packageId'])
				   		);
				   		break;
				}
			}
		}

		public function pushComment($employeeId, $clientId, $comment){
			$this->pd->clients_pushCommentById($employeeId, $clientId, $comment);	
		}

		public function getClientById($id){
			$client 					= $this->pd->getClientById($id);
			$packagesPre				= $this->pd->getClientPackagesById($id);
			$client['packages']			= [];

			// mapping
			foreach ($packagesPre as $index => $package) {
				$dailyCost = $package['price'] / $package['packageLength'];

				$client['packages'][$package['id']] = array(
					'packageName'	=> $package['packageName'],
					'packageId'		=> $package['packageId'],
					'dailyCost' 	=> $dailyCost,
					'daysRemain' 	=> floor($package['balance'] / $dailyCost),
					'isHold'		=> $package['isHold'],
					'comment'		=> $package['comment']
				);
			}

			$client['comments']		= $this->getClientCommentsById($id, 0, 10);
			$client['delivery']		= $this->getClientDelivery($id);

			return $client;
		}

		public function getClientCommentsById($id, $from, $to){
			$clientComments = $this->pd->clients_getCommentsById($id, $from, $to);
			foreach ($clientComments as $key => $comment) {
				if($comment['employeeId'] == $this->user['employeeId']){
					$clientComments[$key]['isMyComment'] = true;
				}
			}

			return $clientComments;
		}

		public function getClientDelivery($id, $from=null, $to=null){
			if(!$from){
				$from = date('Y-m-d', strtotime('-30 days'));
			}
			if(!$to){
				$to = date('Y-m-d', strtotime('+30 days'));
			}

			// print_r($from, $to);exit;

			$result = $this->pd->clients_getDeliveryByClientId($id, $from, $to);

			$months = [];

			foreach ($result as $day) {
				$strtime 	= strtotime($day['date']);
				$monthIndex = date('y', $strtime)."/".date('m', $strtime);

				if(!isset($months[$monthIndex])){
					$months[$monthIndex] = [];
				}

				$months[$monthIndex][] = $day;
			}

			return $months;
		}

		public function setClientDelivery($id, $packageId, $from, $to, $state)
		{
			$from 		= new DateTime($from);
			$to 		= new DateTime($to);
			$now 		= new DateTime();

			if($from < $now || $to < $now){
				return array('success' => false, 'error' => 'only now or future dates availible');
			} else {
				$interval 	= DateInterval::createFromDateString('1 day');
				$period 	= new DatePeriod($from, $interval, $to);
			
				return $this->pd->clients_setDeliveryByClientId($id, $packageId, $period, $state, $this->user['employeeId']);
			}
		}

		public function foreshowChangePackage($clientId, $clientPackageId, $newPackageId){
			function getClosest($search, $arr) {
				$closest = null;
				foreach ($arr as $item) {
					if ($closest === null || abs($search - $closest) > abs($item - $search)) {
						$closest = $item;
					}
				}
			   return $closest;
			}

			$packageData	= $this->pd->clients_foreshowPackage($clientId, $clientPackageId, $newPackageId);
			$packagePrices 	= $this->pd->package_pricesGetByPackageId($newPackageId);

			$balance = $packageData['balance'];

			$result = array(
				'balance' => $balance
			);

			// выводим все значения цены в inline массив
			$pricesArr = array();
			for ($i=0; $i < count($packagePrices); $i++) { 
				$pricesArr[] = $packagePrices[$i]['price'];
			}

			// получаем ближайший по цене пакет
			$closestPriceObj = $packagePrices[array_search(getClosest($balance, $pricesArr), $pricesArr)];

			$pricesLength = count($packagePrices);
			$availiblePrices = array();
			for ($i=0; $i < $pricesLength; $i++) { 
				// check each price to pay
				$item = $packagePrices[$i];
				if($balance - $item['price'] < 0){
					$availiblePrices[] = array(
						'id' 			=> $item['id'],
						'packageLength' => $item['packageLength'],
						'priceToPay' 	=> ($balance - $item['price'])*-1
					);
				}
			}

			$newPackageDailyPrice 		= $closestPriceObj['price'] / $closestPriceObj['packageLength']; //цена пакета за день

			//so we need to fullfill newPackage data
			$result['newPackage'] = array(
				'id' 				=> $newPackageId,
				'name'				=> $packageData['newPackageName'],
				'justEatDays'		=> floor($balance / $newPackageDailyPrice),
				'paymentActions'	=> $availiblePrices
			);

			return $result;
		}

		public function changePackage($employeeId, $oldPackageId, $newPackageId, $newPriceId = 0, $amount = 0, $paymentType = 0){
			// $oldPackageId - idшник пакета в базе client_packages
			// $newPackageId - idшник пакета в базе package_prices
			// $newPriceId - idшник цены пакета в базе package_prices
			// $amount - баланс клиента
			// $paymentType - тип оплаты в базе payment_types (если есть)

			// #говнокод 

			if($amount == 0){
				// меняем пакет без доплаты
				$newPriceId = reset($this->pd->package_getPriceByPackageId($newPackageId));
			}

			$this->pd->clients_changePackage($oldPackageId, $newPackageId, $newPriceId, $amount, $paymentType, $amount == 0);
			$this->pd->history_changePackage($employeeId, $oldPackageId, $newPackageId, $amount, $paymentType, $amount == 0);
		}

		public function payDebt($employeeId, $clientId, $amount, $paymentType)
		{
			// write data to history log
			$this->pd->history_payDebt($employeeId, $clientId, $amount, $paymentType);
			$this->pd->clients_setDebtHold($clientId, true);

			//update data in main table
			// $debtAmmount = $this->pd->clients_getDebtById($clientId)['debt'];
			// $this->pd->clients_payDebt($clientId, ($debtAmmount - $amount), $paymentType);
		}

		public function cancelPayDebt($id, $clientId){
			// write data to history log
			$this->pd->history_cancelDebt($id);
			$this->pd->clients_setDebtHold($clientId, false);
		}


		public function packageProlongation($employeeId, $clientId, $packageId, $amount, $paymentType){
			// write data to history log
			$this->pd->history_prolongation($employeeId, $clientId, $amount, $paymentType);
			$this->pd->clients_setProlongationHold($packageId, true);
		}

		public function cancelProlongation($id, $packageId){
			// write data to history log
			$this->pd->history_deleteProlongation($id);
			$this->pd->clients_setProlongationHold($packageId, false);
		}

		public function logout(){
			session_unset();
			die();
		}
	}
?>