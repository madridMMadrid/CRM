<?php

namespace App;

use \Core\API;
use DateTime;
use \ZMQContext;
use \ZMQ;

/**
 * Class clients
 */
class Client extends API
{
    // MAIN

    /**
     * Client constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function pushSocket($entryData)
    {
        // This is our new stuff
        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://localhost:5555");

        $socket->send(json_encode($entryData));
    }

    // END MAIN


    // CLIENT

    /**
     * Add a client.
     *
     * @param  int    $name
     * @param  string $phone
     * @param  string $email
     * @param  string $city
     * @return integer
     */
    public function store($name, $phone, $email, $city)
    {
        $client = $this->pd->createClient($name, $phone, $email, $city);

        return $client;
    }

    /**
     * Edit the client.
     *
     * @param  int    $clientId
     * @param  string $name
     * @param  string $phone
     * @param  string $email
     * @param  string $city
     * @return bool
     */
    public function edit($clientId, $name, $phone, $email, $city)
    {
        return (bool)$this->pd->editClient($clientId, $name, $phone, $email, $city);
    }

    /**
     * Logout method.
     */
    public function logout()
    {
        session_unset();
        die();
    }

    /**
     * Get all the data of the user.
     *
     * @param  $id
     * @return array|FALSE
     */
    public function getClientById($id)
    {
        $client = $this->pd->getClientById($id);

        $isBlocked = $this->checkIfBlocked($id);

        // Mapping
        $client['request'] = (int)$this->pd->clientHasRequests($id)['id'];          //check is new client or requires attention
        $client['blocked'] = $isBlocked['type'] ? $isBlocked : (bool)$isBlocked['type'];       // Is the client blocked.
        $client['bonuses'] = (int)$this->clientBonusesTotal($id);                  // The client's bonuses.
        $client['city'] = $this->pd->getCityByClientId($id)['name'];               // Get the client's city.
        $client['comments'] = $this->getClientComments($id, 0, 10);      // The client's comments.
        $client['debt'] = $this->pd->clientCountDebtSum($id)['debtSum'];           // Total of all the client's debts.
        $client['gender'] = (int)$client['gender'];                                // The client's gender.
        $client['id'] = (int)$client['id'];                                        // The client's id.
        $client['isDebtHold'] = (bool)$this->pd->isDebtHold($id);                  // Check if the client paid debt.
        $client['isNew'] = (bool)$client['isNew'];                                 // Check if the client is new.
        $client['packages'] = $this->getPackages($id);                             // The client's packages.
        $client['timeZone'] = (int)$this->pd->getCityByClientId($id)['timezone'];  // Get local timezone.

        if ($isBlocked) {
        }

        return $client;
    }

    /**
     * Block the client.
     *
     * @param  int    $clientId
     * @param  int    $type
     * @param  string $comment
     * @return boolean|string
     */
    public function blockClient($clientId, $type, $comment)
    {
        if ($this->checkIfBlocked($clientId)['type'] == $type) {
            return 'It\'s already been done.';
        }

        return $this->pd->blockClient($clientId, $type, $comment, $this->user['employeeId']);
    }

     /**
      * Unblock user.
      *
      * @param  int $clientId
      * @return boolean|string
      */
    public function unblockClient($clientId)
    {
        return $this->pd->unblockClient($clientId);
    }

    /**
     * Check if the user is blocked.
     *
     * @param  int $clientId
     * @return boolean
     */
    public function checkIfBlocked($clientId = null)
    {
        return $this->pd->checkIfBlocked($clientId);
    }

    // END CLIENT


    // STATISTICS

    /**
     * Display all the new clients.
     *
     * @return array
     */
    public function newClients()
    {
        return $this->pd->newClients();
    }

    /**
     * Display all the required clients.
     *
     * @return array
     */
    public function requireAttention()
    {
        $clients = $this->pd->requireAttention();
        $resultArray = array();

        foreach ($clients as $client) {
            $resultArray[$client['id']][] = $client;
        }

        foreach ($resultArray as &$item) {
            if (count($item) > 1) {
                $item = $item[0];
                $item['rate'] = true;
                $item['days'] = true;
            } else {
                $item = $item[0];
            }
        }

        // Unset the reference.
        unset($item);

        // Mapping.
        foreach ($resultArray as &$item) {
            $item['id'] = (int)$item['id'];

            // Check the tag.
            if (isset($item['rate'])) {
                $item['rate'] = (bool)$item['rate'];
            } else {
                $item['rate'] = false;
            }

            // Check the tag.
            if (isset($item['days'])) {
                $item['days'] = (int)$item['days'];
            } else {
                $item['days'] = false;
            }
        }

        // Unset the reference.
        unset($item);

        return $resultArray;
    }

    /**
     * Display all the clients matching the query.
     *
     * @param  int    $limitFrom
     * @param  int    $limitTo
     * @param  string $query
     * @return array
     */
    public function displayClients($limitFrom, $limitTo, $query)
    {
        $limitFrom = $limitFrom ?? 0;
        $limitTo = $limitTo ?? 20;

        return $this->pd->displayClients($limitFrom, $limitTo, $query);
    }

    // END STATISTICS


    // COMMENTS

    /**
     * Add a comment to the user profile.
     *
     * @param  $clientId
     * @param  $comment
     * @return bool
     */
    public function pushComment($clientId, $comment)
    {
        return $this->pd->pushComment($this->user['employeeId'], $clientId, $comment);
    }

    /**
     * Change the comment.
     *
     * @param $clientPackageId
     * @param $comment
     */
    public function changePackageComment($clientPackageId, $comment)
    {
        $this->pd->clientsChangePackageComment($clientPackageId, $comment);
    }

    /**
     * Getting all the comments of the user.
     *
     * @param  $clientId
     * @param  $limitFrom
     * @param  $limitTo
     * @return array
     */
    public function getClientComments($clientId, $limitFrom = 0, $limitTo = 20)
    {
        $limitFrom = $limitFrom ?? 0;
        $limitTo = $limitTo ?? 20;

        $comments = $this->pd->clientGetComments($clientId, $limitFrom, $limitTo);
        foreach ($comments as &$comment) {
            // Mapping (types).
            $comment['employeeId'] = (int)$comment['employeeId'];
            $comment['id'] = (int)$comment['id'];

            if ($comment['employeeId'] == $this->user['employeeId']) {
                $comment['isMyComment'] = true;
            }
        }

        unset($comment);

        return $comments;
    }

    // END COMMENTS


    // SET DELIVERIES

    /**
     * Set a delivery.
     *
     * @param  $clientPackageId
     * @param  $state
     * @param  $from
     * @param  $to
     * @param  $addressId
     * @param  $timeId
     * @return string
     */
    public function setDeliveries($clientPackageId, $state, $from, $to, $addressId = null, $timeId = null)
    {
        $fromDate = new DateTime($from);
        $toDate = new DateTime($to);
        $now = new DateTime();

        // Get client package by.
        $clientPackage = $this->pd->packageGetClientPackage($clientPackageId);

        // Check if the interval is valid.
        if ($fromDate < $now || $toDate < $now) {
            return 'Only current or future dates available.';
        }

        $interval = date_diff($fromDate, $toDate)->days;
        $period = $this->pd->packageGetPeriod($clientPackage['packageId']);
        $dates = array($fromDate->format('Y-m-d'));

        // Freeze!
        if ($state == 1) {
            $this->setFreeze($clientPackageId, $from, $period, $to);

            // Set period to 1.
            $period = 1;
        }

        // All the needed dates.
        for ($i = 1; $i <= $interval / $period; $i++) {
            $dates[$i] = $fromDate->modify("$period day")->format('Y-m-d');
        }

        // NULL the variables.
        $resultAddress = $resultTime = null;

        // Write each date to the database.
        foreach ($dates as $date) {
            // Get day number.
            $dayNumber = date('N', strtotime($date));

            // Check if this is an exceptional time.
            if ($state == 0) {
                if (is_null($addressId)) {
                    $resultAddress = $this->pd->getAddressByWeekDayNumber($clientPackageId, $dayNumber)['id'];
                } else {
                    $resultAddress = $addressId;
                }
                if (is_null($timeId)) {
                    $resultTime = $this->pd->getTimeByWeekDayNumber($clientPackageId, $dayNumber)['id'];
                } else {
                    $resultTime = $timeId;
                }
            }

            // Check if there is a delivery already scheduled for this date.
            $check = $this->pd->getDeliveries($clientPackageId, $date, $date);

            // Store the data.
            if (!$check) {
                $result = $this->pd->setDeliveries(
                    $clientPackageId,
                    $state,
                    $date,
                    $this->user['employeeId'],
                    $resultAddress,
                    $resultTime
                );
            } else {
                $result = $this->changeDelivery($check[0]['id'], $state, $date, $addressId, $timeId);
            }

            // Break if something is wrong.
            if (!$result) {
                return 'Something went wrong!';
            }
        }

        // Return the result.
        return 'It\'s okay.';
    }

    /**
     * Freeze the deliveries.
     *
     * @param $clientPackageId
     * @param $from
     * @param $period
     * @param $to
     */
    public function setFreeze($clientPackageId, $from, $period, $to)
    {
        // Get deliveries overlapping the freezing period.
        $deliveries = $this->getDeliveries($clientPackageId, $from, $to);

        // Check if there are already deliveries.
        if ($deliveries) {
            // Add days.
            if ($period == 2) {
                $addDays = count($deliveries) * 2 - 1;
            } else {
                $addDays = count($deliveries) - 1;
            }

            // here this baby expects next days to be unfilled, but they coud be setted with delivery
            // so we need to find next unfilled day, then fill it with delivery

            // From date.
            $lastDelivery = $this->pd->getLastDeliveryDate($clientPackageId, $to);
            $newFromDate = new DateTime($lastDelivery['deliveryDate']);
            $newFromDate = $newFromDate->modify('1 day')->format('Y-m-d');

            // // To date.
            $newToDate = new DateTime($newFromDate);
            $newToDate = $newToDate->modify($addDays . ' day')->format('Y-m-d');

            // Delete the deliveries.
            $this->deleteDelivery($clientPackageId, $from, $to);

            // Move the deliveries.
            $this->setDeliveries($clientPackageId, 0, $newFromDate, $newToDate);
        }
    }

    /**
     * Change the delivery.
     *
     * @param  $id
     * @param  $state
     * @param  $date
     * @param  $addressId
     * @param  $timeId
     * @return bool
     */
    public function changeDelivery($id, $state, $date, $addressId, $timeId)
    {
        return $this->pd->clientChangeDelivery($id, $state, $date, $addressId, $timeId);
    }

    /**
     * Delete the deliveries matching the query.
     *
     * @param  $clientPackageId
     * @param  $from
     * @param  $to
     * @return array
     */
    public function deleteDelivery($clientPackageId, $from, $to = null)
    {
        $fromDate = new DateTime($from);
        $toDate = new DateTime($to);
        $now = new DateTime();


        // bug, сравнение нужно проводить не учитывая время, может случиться факапчик
        if ($fromDate < $now || (($toDate < $now) && $to)) {
            return array('success' => false, 'error' => 'Only current or future dates available');
        } else {
            return $this->pd->clientDeleteDelivery($clientPackageId, $from, $to);
        }
    }

    /**
     * Get the deliveries.
     *
     * @param  $clientPackageId
     * @param  $dateFrom
     * @param  $dateTo
     * @return array
     */
    public function getDeliveries($clientPackageId, $dateFrom = null, $dateTo = null)
    {
        if ($dateFrom) {
            $dateFrom = date("Y-m-d", strtotime($dateFrom));
        }

        if ($dateTo) {
            $dateTo = date("Y-m-d", strtotime($dateTo));
        }

        $deliveries = $this->pd->getDeliveries($clientPackageId, $dateFrom, $dateTo);

        // Mapping.
        foreach ($deliveries as $key => $delivery) {
            $deliveries[$key]['addressId'] = (int)$delivery['addressId'];
            $deliveries[$key]['clientPackageId'] = (int)$delivery['clientPackageId'];
            $deliveries[$key]['employeeId'] = (int)$delivery['employeeId'];
            $deliveries[$key]['id'] = (int)$delivery['id'];
            $deliveries[$key]['state'] = (int)$delivery['state'];
            $deliveries[$key]['timeId'] = (int)$delivery['timeId'];
        }

        return $deliveries;
    }

    // END DELIVERIES


    // WINDOWS

    /**
     * Show package change window.
     *
     * @param  $clientId
     * @param  $clientPackageId
     * @param  $newPackageId
     * @return array
     */
    public function foreshowChangePackage($clientId, $clientPackageId, $newPackageId)
    {
        // Closest package
        function getClosest($search, $arr)
        {
            $closest = null;
            foreach ($arr as $item) {
                if ($closest === null || abs($search - $closest) > abs($item - $search)) {
                    $closest = $item;
                }
            }
            return $closest;
        }

        $packageData = $this->pd->clientsShowPackage($clientId, $clientPackageId, $newPackageId);
        $packagePrices = $this->pd->packagesGetPricesById($newPackageId);

        $balance = $packageData['balance'];

        $result = array(
            'balance' => (int)$balance,
            'bonuses' => (int)$this->clientBonusesTotal($clientId)
        );

        // Store all the values.
        $pricesArr = array();
        for ($i = 0; $i < count($packagePrices); $i++) {
            $pricesArr[] = $packagePrices[$i]['price'];
        }

        // Get the next cheapest package
        $closestPriceObj = $packagePrices[array_search(getClosest($balance, $pricesArr), $pricesArr)];

        $pricesLength = count($packagePrices);
        $availablePrices = array();
        for ($i = 0; $i < $pricesLength; $i++) {
            // Check each price to pay
            $item = $packagePrices[$i];
            if ($balance - $item['price'] < 0) {
                $availablePrices[] = array(
                    'id' => $item['id'],
                    'packageLength' => $item['packageLength'],
                    'priceToPay' => ($balance - $item['price']) * -1,
                );
            }
        }

        $newPackageDailyPrice = $closestPriceObj['price'] / $closestPriceObj['packageLength'];

        // New package data
        $result['newPackage'] = array(
            'id' => $newPackageId,
            'name' => $packageData['newPackageName'],
            'justEatDays' => floor($balance / $newPackageDailyPrice),
            'paymentActions' => $availablePrices,
        );

        return $result;
    }

    /**
     * Get prolongation options.
     *
     * @param  $clientPackageId
     * @return array
     */
    public function showProlongPackage($clientPackageId)
    {
        $clientPackage = $this->pd->packageGetClientPackage($clientPackageId);
        $packageData = $this->pd->clientsShowPackage(
            $clientPackage['clientId'],
            $clientPackageId,
            $clientPackage['packageId']
        );
        $packagePrices = $this->pd->packagesGetPricesById($clientPackage['packageId']);

        $balance = $packageData['balance'];

        $result = array(
            'balance' => (int)$balance,
            'bonuses' => (int)$this->clientBonusesTotal($clientPackage['clientId'])
        );

        // An array containing all the prices.
        $pricesArr = array();
        for ($i = 0; $i < count($packagePrices); $i++) {
            $pricesArr[] = $packagePrices[$i]['price'];
        }

        $pricesLength = count($packagePrices);
        $availablePrices = array();
        for ($i = 0; $i < $pricesLength; $i++) {
            // Check if the user has enough money.
            $item = $packagePrices[$i];
            $availablePrices[] = array(
                'id' => (int)$item['id'],
                'packageLength' => (int)$item['packageLength'],
                'priceToPay' => (int)$item['price'],
            );
        }

        $result['options'] = $availablePrices;

        return $result;
    }

    /**
     * Display adding options.
     *
     * @param  $clientId
     * @param  $packageId
     * @return array
     */
    public function showAddPackage($clientId, $packageId)
    {
        $options = $this->pd->packagesGetPricesById($packageId);

        foreach ($options as &$option) {
            $option['id'] = (int)$option['id'];
            $option['packageLength'] = (int)$option['packageLength'];
            $option['price'] = (int)$option['price'];
        }

        // Unset the reference.
        unset($option);

        $result = array(
            'options' => $options,
            'bonuses' => (int)$this->clientBonusesTotal($clientId)
        );

        return $result;
    }

    // END WINDOWS


    // PACKAGES

    /**
     * Wrapper.
     *
     * @param  array $order
     * @param  array $addresses
     * @param  array $times
     * @return bool
     */
    public function addWrapper($order, $addresses, $times)
    {
        $addPackage = $this->addPackage(
            $order['clientId'],
            $order['packageId'],
            $order['priceId'],
            $order['amount'],
            $order['paymentType']
        );
        $addAddresses = $this->addAddress($addresses, $addPackage);
        $addTimes = $this->setTime($times, $addPackage);
        $length = $this->pd->packageGetPriceById($order['priceId'])['packageLength'];

        $dateTo = (new DateTime($order['dateFrom']))->modify($length - 1 . ' day')->format('Y-m-d');
        $setDelivery = $this->setDeliveries($addPackage, 0, $order['dateFrom'], $dateTo);

        return $addPackage && $addAddresses && $addTimes && $setDelivery;
    }

    /**
     * Add a new package.
     *
     * @param  $clientId
     * @param  $packageId
     * @param  $priceId
     * @param  $amount
     * @param  $paymentType
     * @param  null        $bonus
     * @return bool|string
     */
    public function addPackage($clientId, $packageId, $priceId, $amount, $paymentType, $bonus = null)
    {
        // Add a package.
        $clientPackage = $this->pd->clientAddPackage($clientId, $packageId, $priceId);

        $amount -= $this->countBonus($clientId, $bonus);

        // Add a new payment.
        $this->pd->payment($clientPackage, $packageId, $this->user['employeeId'], $amount, $paymentType, 2);
        $this->pd->history($clientPackage, $packageId, $this->user['employeeId'], $amount, $paymentType, 2);
        $this->pd->fulfillBalance($bonus, $clientPackage);

        $this->pd->processSuccess($clientId);

        return $clientPackage;
    }

    /**
     * Change package and write the changes to the log table.
     *
     * @param  $clientPackageId
     * @param  $packageId
     * @param  $priceId
     * @param  $amount
     * @param  $paymentType
     * @param  null            $bonus
     * @return string
     */
    public function changePackage($clientPackageId, $packageId, $priceId, $amount, $paymentType, $bonus = null)
    {
        // Get the package.
        $clientPackage = $this->pd->packageGetClientPackage($clientPackageId);

        $amount -= $this->countBonus($clientPackage['clientId'], $bonus);

        // Change the package.
        $this->pd->changePackage($clientPackageId, $packageId, $priceId);

        // Write the action to the history table.
        $this->pd->history(
            $clientPackageId,
            $packageId,
            $this->user['employeeId'],
            $amount,
            $paymentType,
            0
        );

        // Save the payment.
        $this->pd->payment(
            $clientPackageId,
            $packageId,
            $this->user['employeeId'],
            $amount,
            $paymentType,
            0
        );

        $this->pd->processSuccess($clientPackage['clientId']);

        return 'It\'s okay.';
    }

    /**
     * Prolong the package and write the changes to the log table.
     *
     * @param  $clientPackageId
     * @param  $priceId
     * @param  $amount
     * @param  $paymentType
     * @param  null            $bonus
     * @return string
     */
    public function packageProlongation($clientPackageId, $priceId, $amount, $paymentType, $bonus = null)
    {
        // Get the package.
        $clientPackage = $this->pd->packageGetClientPackage($clientPackageId);

        $amount -= $this->countBonus($clientPackage['clientId'], $bonus);

        // Change the package.
        $this->pd->changePackage($clientPackageId, $clientPackage['packageId'], $priceId);

        // Log it.
        $this->pd->history(
            $clientPackageId,
            $clientPackage['packageId'],
            $this->user['employeeId'],
            $amount,
            $paymentType,
            1
        );

        // Add a payment.
        $this->pd->payment(
            $clientPackageId,
            $clientPackage['packageId'],
            $this->user['employeeId'],
            $amount,
            $paymentType,
            1
        );

        $this->pd->processSuccess($clientPackage['clientId']);

        return 'It\'s okay.';
    }

    /**
     * Cancel all the changes and delete it from the log table.
     *
     * @param $clientPackageId
     */
    public function cancelPackageChange($clientPackageId)
    {
        // Get the data of the client's package.
        $clientPackage = $this->pd->packageGetClientPackage($clientPackageId);

        // Check if the package is to be deleted.
        $isDelete = $this->pd->clientCheckDelete($clientPackageId);

        // Delete the package.
        if (!is_null($isDelete)) {
            $this->pd->clientDeletePackage($clientPackageId);
        }

        // Write to the history.
        $this->pd->history(
            $clientPackageId,
            $clientPackage['packageId'],
            $this->user['employeeId'],
            null,
            null,
            3
        );

        // Cancel payment.
        $this->pd->paymentCancel($clientPackageId);
    }

    /**
     * @param $clientPackageId
     */
    public function confirmPackage($clientPackageId)
    {
        $this->pd->confirmPackage($clientPackageId);
    }

    /**
     * Get the client's packages.
     *
     * @param  $clientId
     * @return array
     */
    public function getPackages($clientId)
    {
        $packagesPre = $this->pd->clientGetPackages($clientId);
        $client['packages'] = [];

        // Mapping.
        foreach ($packagesPre as $index => $package) {
            // Types.
            $package['balance'] = (int)$package['balance'];
            $package['id'] = (int)$package['id'];
            $package['packageId'] = (int)$package['packageId'];
            $package['packageLength'] = (int)$package['packageLength'];
            $package['period'] = (int)$package['period'];
            $package['price'] = (int)$package['price'];
            $package['priceId'] = (int)$package['priceId'];

            $dailyCost = $package['price'] / $package['packageLength'];

            $client['packages'][$package['id']] = array_merge(
                $package,
                array(
                    'action' => $this->getPackageAction($package['id']),
                    'addresses' => $this->pd->getPackageAddresses($package['id']),
                    'dailyCost' => $dailyCost,
                    'daysRemain' => floor($package['balance'] / $dailyCost),
                    'deliveryTime' => $this->getTimes($package['id']),
                    'deliveries' => $this->getDeliveries($package['id']),
                    'debt' => (int)$this->pd->clientGetPackageDebt($package['id'])['debt'],
                    'isHold' => $this->pd->isPackageHold($package['id'])
                )
            );
        }

        return $client['packages'];
    }

    // END PACKAGES


    // HISTORY

    /**
     * Get the user's history.
     *
     * @param  int $clientId  Client's ID.
     * @param  int $query     Search query.
     * @param  int $limitFrom First element's ID.
     * @param  int $limitTo   Number of elements.
     * @return array|false
     */
    public function getHistory($clientId, $query, $limitFrom, $limitTo)
    {
        $limitFrom = $limitFrom ?? 0;
        $limitTo = $limitTo ?? 20;

        $records = $this->pd->clientGetHistory($clientId, $query, $limitFrom, $limitTo);

        // Mapping.
        foreach ($records as &$record) {
            $record['actionType'] = (int)$record['actionType'];
            $record['amount'] = (int)$record['amount'];
            $record['clientPackageId'] = (int)$record['clientPackageId'];
            $record['employeeId'] = (int)$record['employeeId'];
            $record['id'] = (int)$record['id'];
            $record['packageId'] = (int)$record['packageId'];
            $record['paymentType'] = (int)$record['paymentType'];
        }

        unset($record);

        return $records;
    }

    // END HISTORY


    // ADDRESSES

    /**
     * Add the user's address.
     *
     * @param  array $addresses
     * @param  int   $clientPackageId
     * @return bool|FALSE|resource
     */
    public function addAddress($addresses, $clientPackageId)
    {
        foreach ($addresses as $address) {
            // Check if the time for this day is set.
            $isSet = $this->pd->getAddressByWeekDayNumber($clientPackageId, $address['weekDay']);

            // Store the data.

            // Store the data.
            if ($isSet) {
                $result = $this->pd->clientChangeAddress(
                    $clientPackageId,
                    $address['city'],
                    $address['street'],
                    $address['building'],
                    $address['latitude'],
                    $address['longitude'],
                    $address['flat'],
                    $address['entrance'],
                    $address['comment'],
                    $address['weekDay'],
                    $isSet['id']
                );
            } else {
                $result = $this->pd->clientAddAddress(
                    $clientPackageId,
                    $address['city'],
                    $address['street'],
                    $address['building'],
                    $address['latitude'],
                    $address['longitude'],
                    $address['flat'],
                    $address['entrance'],
                    $address['comment'],
                    $address['weekDay']
                );
            }
        }

        return $result;
    }

     /**
      * Add the user's address exclusion (once used one).
      *
      * @param  array $addresses
      * @param  array $deliveryIds
      * @return bool|FALSE|resource
      */
    public function addAddressExclusion($address, $deliveryIds)
    {
        $this->pd->clientAddAddress(
            $address['clientPackageId'],
            $address['city'],
            $address['street'],
            $address['building'],
            $address['latitude'],
            $address['longitude'],
            $address['flat'],
            $address['entrance'],
            $address['comment'],
            null
        );


        $string = "";

        foreach ($deliveryIds as $did) {
            $string = $string.$did.", ";
        }

        return $this->pd->deliveryExclusions($this->pd->clientGetLastAddress()['id'], $string);
    }

     /**
      * Add the user's time exclusion (once used one).
      *
      * @param  array $time
      * @param  int   $deliveryIds
      * @return bool|FALSE|resource
      */
    public function addTimeExclusion($time, $deliveryIds)
    {
        $this->pd->clientSetTime($time['clientPackageId'], $time['start'], $time['finish'], $time['interval'], null);


        $string = "";

        foreach ($deliveryIds as $did) {
            $string = $string.$did.", ";
        }

        return $this->pd->deliveryTimeExclusions($this->pd->clientGetLastTime()['id'], $string);
    }

    /**
     * Delete the address.
     *
     * @param $id
     */
    public function deleteAddress($id)
    {
        $this->pd->clientDeleteAddress($id);
    }

    /**
     * Get all the user's addresses.
     *
     * @param  $clientPackageId
     * @return array
     */
    public function getAddresses($clientPackageId)
    {
        $addresses = $this->pd->getPackageAddresses($clientPackageId);

        // Mapping.
        foreach ($addresses as $key => $item) {
            $addresses[$key]['id'] = (int)$item['id'];
            $addresses[$key]['latitude'] = (double)$item['latitude'];
            $addresses[$key]['longitude'] = (double)$item['longitude'];
            $addresses[$key]['weekDay'] = (int)$item['weekDay'];
        }

        return $addresses;
    }

    // END ADDRESSES


    // TIMES

    /**
     * Set new delivery time.
     *
     * @param $times
     */
    public function setTime($times, $clientPackageId)
    {
        foreach ($times as $time) {
            // Check if the time for this day is set.
            $isSet = $this->pd->getTimeByWeekDayNumber($clientPackageId, $time['weekDay']);

            // Store the data.
            if ($isSet) {
                $result = $this->pd->clientChangeTime($time['start'], $time['finish'], $time['interval'], $isSet['id']);
            } else {
                $result = $this->pd->clientSetTime(
                    $clientPackageId,
                    $time['start'],
                    $time['finish'],
                    $time['interval'],
                    $time['weekDay']
                );
            }
        }

        return $result;
    }

    /**
     * Unset the time.
     *
     * @param  $id
     * @return boolean
     */
    public function unsetTime($id)
    {
        return $this->pd->clientUnsetTime($id);
    }

    /**
     * Change the time.
     *
     * @param  $start
     * @param  $finish
     * @param  $interval
     * @param  $id
     * @return boolean
     */
    public function changeTime($start, $finish, $interval, $id)
    {
        return $this->pd->clientChangeTime($start, $finish, $interval, $id);
    }

    /**
     * Get the client's delivery times.
     *
     * @param  $clientPackageId
     * @return array
     */
    public function getTimes($clientPackageId)
    {
        $times = $this->pd->getPackageTimes($clientPackageId);

        // Mapping.
        foreach ($times as $key => $time) {
            $times[$key]['interval'] = (int)$time['interval'];
            $times[$key]['weekDay'] = (int)$time['weekDay'];
        }

        return $times;
    }

    /**
     * Get action from payments.
     *
     * @param  $clientPackageId
     * @return array|FALSE
     */
    public function getPackageAction($clientPackageId)
    {
        $action = $this->pd->getPackageAction($clientPackageId);

        if ($action['actionType']) {
            $action['actionType'] = (int)$action['actionType'];
        }

        return $action;
    }

    // END TIMES


    // CERTIFICATES

    /**
     * Add a certificate.
     *
     * @param  $expiration
     * @param  $comment
     * @param  $discount
     * @param  $clientId
     * @return bool
     */
    public function addCertificate($expiration, $comment, $discount, $clientId)
    {
        // Check if the number is already taken.
        do {
            $number = rand(1000000, 9999999);
        } while ($this->pd->getCertificate($number));

        // Database query.
        return $this->pd->clientAddCertificate($expiration, $comment, $discount, $clientId, $number);
    }

    /**
     * Get the certificate.
     *
     * @param  int $certificateNumber
     * @return array|FALSE
     */
    public function getCertificate($certificateNumber, $clientId)
    {
        if (count($this->pd->certificatesClients($certificateNumber, $clientId)) > 0) {
            return array('validation' => 'Already activated', 'code' => 0);
        } else {
            $certificate = $this->pd->getCertificate($certificateNumber, $clientId);

            if ($certificate) {
                return $certificate;
            } else {
                return array('validation' => 'Not found', 'code' => 1);
            }
        }
    }

    /**
     * Get all the certificates.
     *
     * @param  $limitFrom
     * @param  $limitTo
     * @param  $query
     * @return array|FALSE
     */
    public function getAllCertificates($limitFrom, $limitTo, $query)
    {
        $limitFrom = $limitFrom ?? 0;
        $limitTo = $limitTo ?? 20;
        $query = $query ?? '';

        return $this->pd->clientGetAllCertificates($limitFrom, $limitTo, $query);
    }

    /**
     * Activate the certificate.
     *
     * @param  int $certificateNumber
     * @param  int $clientId
     * @return array|string
     */
    public function activateCertificate($certificateNumber, $clientId)
    {
        // Check if the certificate is attached to another user.
        if ($this->certificateCheckAttachment($certificateNumber, $clientId)) {
            return array('validation' => 'This certificate is attached to another user.');
        }

        // Check if the certificate is available.
        if ($this->certificateCheckActivation($certificateNumber, $clientId)) {
            return array('validation' => 'This one is already activated.');
        }

        $certificate = $this->getCertificate($certificateNumber, $clientId);
        $bonus = $this->setBonus($clientId, $certificate['certificateDiscount']);
        $activate = $this->pd->activateCertificate($certificateNumber, $clientId);
        $history = $this->pd->history(
            null,
            null,
            $this->user['employeeId'],
            $certificate['certificateDiscount'],
            null,
            6
        );

        return array(
            'Charge bonuses' => $bonus ? 'done' : 'fail',
            'Activate certificate' => $activate ? 'done' : 'fail',
            'Write to history' => $history ? 'done' : 'fail'
        );
    }

    /**
     * Check if the certificate is attached to a user.
     *
     * @param  int $certificateNumber
     * @param  int $clientId
     * @return bool
     */
    public function certificateCheckAttachment($certificateNumber, $clientId)
    {
        $certificate = $this->getCertificate($certificateNumber, $clientId);
        return $certificate['clientId'] == $clientId && $clientId;
    }

    /**
     * Check if the certificate is already activated.
     *
     * @param  int $certificateNumber
     * @param  int $clientId
     * @return bool
     */
    public function certificateCheckActivation($certificateNumber, $clientId)
    {
        $certificate = $this->getCertificate($certificateNumber, $clientId);
        // print_r($certificate);
        // exit;
        if (!$certificate['clientId']) {
            return $this->certificatesClients($certificateNumber, $clientId);
        }

        return $this->certificatesClients($certificateNumber);
    }

    /**
     * Get activations.
     *
     * @param  int $certificateNumber
     * @param  int $clientId
     * @return bool
     */
    public function certificatesClients($certificateNumber, $clientId = null)
    {
        return !empty($this->pd->certificatesClients($certificateNumber, $clientId));
    }

    // END CERTIFICATES


    // REQUESTS

    /**
     * Get the request.
     *
     * @param  $limitFrom
     * @param  $limitTo
     * @return array
     */
    public function getRequests($limitFrom, $limitTo)
    {
        $limitFrom = $limitFrom ?? 0;
        $limitTo = $limitTo ?? 20;

        $requests = $this->pd->getRequests($limitFrom, $limitTo);

        foreach ($requests as &$request) {
            $request['id'] = (int)$request['id'];
            $request['packageId'] = (int)$request['packageId'];
        }

        unset($request);

        return $requests;
    }

    /**
     * Process the request.
     *
     * @param  int $requestId
     * @param  int $action
     * @param  int $comment
     * @return bool
     */
    public function processRequest($clientId, $reason, $comment)
    {
        // if ($action && $reason) {
        //     return 'Reason is redundant.';
        // } elseif (!$action && !$reason) {
        //     return 'What is the reason?';
        // }

        $result = null;

        $requestId = $this->pd->clientHasRequests($clientId);
        // print_r($requestId);exit;

        if ($requestId) {
            // this is a request
            $result = $this->pd->processRequest($clientId, 1, $comment, $this->user['employeeId'], $reason);
        } else {
            // this requires attention
            $result = $this->pd->processAttention($clientId, $reason, $this->user['employeeId']);
        }

        $this->pushSocket(array('category' => 'workerOn'));
        $this->pushSocket(array('category' => 'sale'));

        return $result;
    }

    public function clientHasUnprocessedRequests($clientId)
    {
        return $this->pd->clientHasUnprocessedRequests($clientId);
    }

    /**
     * List all the reasons.
     *
     * @return array
     */
    public function showReasons()
    {
        $reasons = $this->pd->showReasons();

        foreach ($reasons as &$reason) {
            $reason['id'] = (int)$reason['id'];
        }

        unset($reason);

        return $reasons;
    }

    /**
     * Add refuse reason (sales headquoter only).
     *
     * @param  string $reason
     * @return bool
     */

    public function addReason($reason)
    {
        return $this->pd->addReason($reason);
    }

     /**
      * Remove refuse reason (sales headquoter only).
      *
      * @param  string $reasonId
      * @return bool
      */

    public function removeReason($reasonId)
    {
        return $this->pd->removeReason($reasonId);
    }



    // END REQUESTS


    // REMINDERS

    /**
     * Defer the request.
     *
     * @param  int $requestId
     * @return FALSE|resource
     */
    public function defer($requestId)
    {
        $reminders = $this->pd->getRemindersByRequestId($requestId);

        if (count($reminders) === 0) {
            $date = date('Y-m-d H:i:s', strtotime("+30 minutes"));
            $result = $this->pd->addReminder($requestId, $date);
        } elseif (count($reminders) === 1) {
            $date = date('Y-m-d H:i:s', strtotime("+1 day"));
            $result = $this->pd->addReminder($requestId, $date);
        } else {
            $result = $this->pd->archiveRequest($requestId);
        }

        return $result;
    }

    /**
     * Get all the reminders.
     *
     * @return array
     */
    public function getReminders()
    {
        $requests = $this->pd->getReminders();

        foreach ($requests as &$request) {
            $request['id'] = (int)$request['id'];
            $request['gender'] = (int)$request['gender'];
            $request['isNew'] = (int)$request['isNew'];
        }

        unset($request);

        return $requests;
    }

    // END REQUESTS


    // PAYMENTS

    /**
     * Confirm the payment.
     *
     * @param  int $paymentId
     * @param  int $amount
     * @param  int $paymentType
     * @return FALSE|resource
     */
    /**
     * Get all the payments matching the query.
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @param  int    $isConfirmed
     * @param  string $query
     * @param  int    $limitFrom
     * @param  int    $limitTo
     * @return array
     */
    public function getPayments($dateFrom, $dateTo, $query = '', $limitFrom = 0, $limitTo = 20)
    {
        $limitFrom = $limitFrom ?? 0;
        $limitTo = $limitTo ?? 20;

        // Dates check.
        if (isset($dateFrom) && !isset($dateTo)) {
            return array('success' => false,
                'error' => 'You should either fill both "from" and "to" fields or not touch them at all!');
        }

        // Interval check.
        if ($dateFrom > $dateTo) {
            return array('success' => false, 'error' => 'The interval given is invalid!');
        }

        $payments = $this->pd->clientGetPayments($dateFrom, $dateTo, $query, $limitFrom, $limitTo);

        // Mapping.
        foreach ($payments as &$payment) {
            $payment['amount'] = (int)$payment['amount'];
            $payment['clientId'] = (int)$payment['clientId'];
            $payment['clientPackageId'] = (int)$payment['clientPackageId'];
            $payment['paymentId'] = (int)$payment['paymentId'];
            $payment['paymentType'] = (int)$payment['paymentType'];
        }

        unset($payment);

        return $payments;
    }

    
    public function confirmPayment($paymentId, $amount, $paymentType)
    {
        $payment = $this->pd->clientGetPayment($paymentId);
        $package = $this->pd->packageGetClientPackage($payment['clientPackageId']);
        $debt = $this->pd->clientGetDebtByClientPackage($payment['clientPackageId']);

        if ($payment['amount'] > $amount) {
            if ($debt) {
                $this->pd->clientChangeDebt($debt['id'], $debt['amount'] + $payment['amount'] - $amount);
            } else {
                $this->pd->clientAddDebt(
                    $payment['clientPackageId'],
                    $payment['amount'] - $amount,
                    $payment['id'],
                    0
                );
            }

            // Add the debt to the history table.
            $this->pd->history(
                $payment['clientPackageId'],
                $payment['packageId'],
                $this->user['employeeId'],
                $amount,
                $paymentType,
                6
            );
        } elseif ($payment['amount'] < $amount) {
            $this->pd->clientAddDebt(
                $payment['clientPackageId'],
                ($payment['amount'] - $amount) * -1,
                $payment['id'],
                1
            );
            $amount = $payment['amount'];
        }

        // Fulfill the balance.
        $this->pd->fulfillBalance($amount, $package['id']);

        // Fulfil the client's bonuses
        if (in_array($payment['actionType'], array(0, 1, 2))) {
            $bonuses = (bool)$this->pd->getBonuses($package['clientId']);
            if (!$bonuses) {
                $this->pd->createBonus($package['clientId'], $amount * 0.05);
            } else {
                $this->pd->increaseBonus($package['clientId'], $amount * 0.05);
            }
        }

        $this->pushSocket(array('category' => 'sale'));

        return $this->pd->clientConfirmPayment($paymentId, $paymentType);
    }

    // END PAYMENTS


    // DEBTS

    /**
     * Pay the debt.
     *
     * @param  $amount
     * @param  $clientId
     * @param  $paymentType
     * @return array|bool
     */
    public function payDebt($amount, $clientId, $paymentType)
    {
        // Get client's total debt.
        $clientDebt = $this->pd->clientCountDebtSum($clientId)['debtSum'];

        // Check if the client pays not more than he or she owes.
        if ($amount > $clientDebt) {
            return 'You are trying to pay more than you owe us.';
        }

        // Get all the debts.
        $debts = $this->pd->getDebtsByClient($clientId);

        foreach ($debts as $debt) {
            if ($amount > 0) {
                $resultAmount = $amount >= $debt['amount'] ? $debt['amount'] : $amount;

                // Check if there is at least some money.
                if (!$resultAmount) {
                    return array('success' => false, 'error' => 'You should pay at least some money.');
                }

                // Decrease the sum of the debt.
                $this->pd->clientChangeDebt($debt['id'], $debt['amount'] - $resultAmount);

                // Return the result.
                $result = $this->pd->payment(
                    $debt['clientPackageId'],
                    null,
                    $this->user['employeeId'],
                    $resultAmount,
                    $paymentType,
                    5
                );

                if (!$result) {
                    return array('success' => false, 'error' => 'Something went wrong. :(');
                }

                $amount -= $debt['amount'];
            }
        }

        return true;
    }

    // END DEBTS


    /**
     * Return the cooking list.
     *
     * @return array
     */
    public function cookingList()
    {
        $date = $date = date('Y-m-d');

        // Withdraw money from clients.
        $this->withdrawBalance();

        // Return the result.
        return $this->getDeliveries(null, $date, $date);
    }

    /**
     * Withdraw money.
     */
    public function withdrawBalance()
    {
        $date = $date = date('Y-m-d');

        // Get all the today's deliveries.
        $deliveries = $this->getDeliveries(null, $date, $date);

        // Withdraw money.
        foreach ($deliveries as $delivery) {
            $this->pd->withdrawBalance($delivery['clientPackageId']);
        }
    }

    /**
     * Get my stats.
     *
     * @return array|FALSE
     */
    public function myStats()
    {
        // Get all the stats.
        $employees = $this->pd->myStats();

        // Mapping.
        foreach ($employees as $key => $field) {
            $employees[$key]['canceled'] = (int)$employees[$key]['canceled'];
            $employees[$key]['success'] = (int)$employees[$key]['success'];
            $employees[$key]['sum'] = (int)$employees[$key]['sum'];
        }

        // Return the result.
        return $employees;
    }


    // BONUSES

    /**
     * Count the client's bonus.
     *
     * @param  $clientId
     * @param  $bonus
     * @return int
     */
    public function countBonus($clientId, $bonus)
    {
        if ($bonus) {
            if ($bonus <= $this->clientBonusesTotal($clientId)) {
                $result = $this->withdrawBonuses($bonus, $clientId);
                if ($result) {
                    return $bonus;
                }
            }
        }
        return 0;
    }

    /**
     * Get all the client's bonuses.
     *
     * @param  $clientId
     * @return array
     */
    public function getBonuses($clientId)
    {
        // Getting all the bonuses.
        $bonuses = $this->pd->getBonuses($clientId);

        // Mapping.
        foreach ($bonuses as &$bonus) {
            $bonus['amount'] = (int)$bonus['amount'];
            $bonus['id'] = (int)$bonus['id'];
        }

        // Unset the reference.
        unset($bonus);

        // Return the result.
        return $bonuses;
    }

    /**
     * Get the client's bonuses (total).
     *
     * @param  $clientId
     * @return int
     */
    public function clientBonusesTotal($clientId)
    {
        return (int)$this->pd->clientBonusesTotal($clientId);
    }

    /**
     * Withdraw the client's bonuses.
     *
     * @param  $amount
     * @param  $clientId
     * @return string
     */
    public function withdrawBonuses($amount, $clientId)
    {
        $bonuses = $this->pd->getBonuses($clientId);
        foreach ($bonuses as $bonus) {
            if ($amount > 0) {
                $resultAmount = $amount >= $bonus['amount'] ? $bonus['amount'] : $amount;

                // Change the bonus.
                $result = $this->pd->withdrawBonuses($bonus['id'], $bonus['amount'] - $resultAmount);

                // Check if everything is okay.
                if (!$result) {
                    return 'Something went wrong. :(';
                }

                // Decrease the amount.
                $amount -= $bonus['amount'];
            }
        }

        return 'It\'s okay';
    }

    /**
     * Set a bonus.
     *
     * @param  int    $clientId
     * @param  int    $amount
     * @param  string $expiration
     * @param  string $comment
     * @return bool
     */
    public function setBonus($clientId, $amount, $expiration = null, $comment = null)
    {
        $bonuses = (bool)$this->pd->getBonuses($clientId);

        if (!$bonuses) {
            $result = $this->pd->createBonus($clientId, $amount, $expiration, $comment);
        } else {
            if ($expiration) {
                $result = $this->pd->createBonus($clientId, $amount, $expiration, $comment);
            } else {
                $result = $this->pd->increaseBonus($clientId, $amount);
            }
        }

        return (bool)$result;
    }

    // END BONUSES


    // CITIES

    /**
     * Get cities.
     *
     * @return array
     */
    public function getCities()
    {
        $cities = $this->pd->getCities();

        foreach ($cities as &$city) {
            $city['id'] = (int)$city['id'];
            $city['timezone'] = (int)$city['timezone'];
        }

        unset($city);

        return $cities;
    }

    // END CITIES

    public function checkClientIsBusy($id)
    {
        $business = $this->pd->checkClientIsBusy($id);
        $busyValue = !is_null($business);

        if (!$business) {
            $this->pd->setClientBusy($id, $this->user['employeeId']);
            $this->pushSocket(array('category' => 'workerOn'));
            $busyValue = false;
        }

        return array('busy' => $busyValue, 'employee' => $business);
    }

    public function removeClientBusiness($id)
    {
        $this->pd->removeClientBusiness($id);
        $this->pushSocket(array('category' => 'workerOn'));
    }

    // Refunds

    public function foreshowRefund($clientPackageId)
    {
        $package = $this->pd->packageGetClientPackage($clientPackageId);
        return array(
            'balance' => (int)$package['balance'],
            'withFee' => (int)$package['balance'] / 100 * 75
        );
    }

    public function addRefund($clientPackageId, $comment)
    {
        $this->pd->addClientRefund($clientPackageId, $this->user['employeeId'], $comment);
    }

    public function showRefunds()
    {
        return $this->pd->showRefunds();
    }

    public function confirmRefund($refundId)
    {
        $clientPackageId = $this->pd->getClientPackageIdByRefundId($refundId)['client_package_id'];

        // обнуляем баланс
        $nomoney = $this->pd->withdrawBalance($clientPackageId);

        // удаляем пакет
        $nopackage = $this->pd->clientDeletePackage($clientPackageId);

        // потенциально тонкое мсето, зависимо от $this->deleteDelivery
        // удаляем доставки
        $deletedDeliveries = $this->deleteDelivery($clientPackageId, date("Y-m-d", strtotime('+1 day')), date('Y-m-d', strtotime('+10 years')));

        return $this->pd->confirmRefund($refundId);
    }

    public function cancelRefund($refundId)
    {
        return $this->pd->cancelRefund($refundId);
    }

    // End Refunds
}
