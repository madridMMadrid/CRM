<?php

namespace Core;

use Core\Config\Database;
use \ZMQContext;
use \ZMQ;

/**
 * Class ParseData
 *
 * @package Core
 */
class ParseData extends Database
{

    public function pushSocket($entryData)
    {
        // This is our new stuff
        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://localhost:5555");

        $socket->send(json_encode($entryData));
    }

    /**
     * Login method.
     *
     * @param  $username
     * @param  $password
     * @return array|FALSE
     */
    public function login(string $username, string $password): array
    {
        $sql = '
            SELECT
              id,
              username,
              `password`,
              type,
              picture,
              `name`
            FROM employees
            WHERE username = ?s AND PASSWORD = ?s
        ';

        return $this->db->getAll($sql, $username, $password);
    }

    /// CLIENTS SECTION ///

    /**
     * Getting all the new clients.
     *
     * @return array
     */
    public function newClients()
    {
        return $this->db->getAll(
            '
            SELECT
                id,
                `name`,
                phone,
                email,
                gender,
                is_new AS isNew
            FROM clients
            WHERE is_new = 1
        '
        );
    }

    /**
     * Check client is new
     *
     * @return array
     */
    public function clientHasRequests($clientId)
    {
        $sql = '
            SELECT id 
            FROM client_requests
            WHERE client_id = ?i
            LIMIT 1
        ';

        return $this->db->getRow($sql, $clientId);
    }

    public function clientHasUnprocessedRequests($clientId)
    {
        $sql = '
            SELECT id 
            FROM client_requests
            WHERE client_id = ?i AND processed is NULL AND reason is NULL
            LIMIT 1
        ';

        return $this->db->getRow($sql, $clientId);
    }


    /**
     * Getting all the clients who have 3 days left.
     *
     * @return array
     */
    public function requireAttention()
    {
        // SQL query.
        $sql = '
            SELECT
              x.id,
              x.name,
              x.phone,
              x.email,
                dpd.employee_id AS currentEmployeeWorks,
                emp.name AS employeeName,
                emp.picture AS employeePicture,
              y.daysLeft AS days,
              NULL       AS rate
            FROM clients x
                LEFT JOIN client_manager_dependance dpd ON x.id = dpd.client_id
                LEFT JOIN employees emp ON dpd.employee_id = emp.id
              JOIN
              (SELECT
                 clients.id,
                 (client_packages.balance DIV (package_prices.price DIV package_prices.package_length)) AS daysLeft
               FROM client_packages
                 JOIN package_prices
                   ON client_packages.price_id = package_prices.id
                 JOIN clients ON client_packages.client_id = clients.id
                 JOIN client_payments ON client_payments.client_package_id = client_packages.id
               WHERE package_length >= 6
                     AND client_packages.deleted = 0
                     AND client_packages.confirmed = 1
                    AND client_payments.is_confirmed = 1
               HAVING daysLeft <= 3) y
                ON x.id = y.id
            WHERE x.refuse_reason_id IS NULL
            GROUP BY x.id
            
            # UNION
            
            # RATE THE PACKAGE
            # SELECT
            #   clients.id,
            #   clients.name,
            #   clients.phone,
            #   clients.email,
            #   dpd.employee_id AS currentEmployeeWorks,
            #     emp.name AS employeeName,
            #     emp.picture AS employeePicture,
            #   NULL          AS days,
            #   TRUE          AS rate
            # FROM client_packages
            #     LEFT JOIN client_manager_dependance dpd ON x.id = dpd.client_id
            #     LEFT JOIN employees emp ON dpd.employee_id = emp.id
            #   JOIN package_prices
            #     ON client_packages.price_id = package_prices.id
            #   JOIN clients ON client_packages.client_id = clients.id
            # WHERE package_length = 1
            #       AND client_packages.deleted = 0
            #       AND client_packages.confirmed = 1
            #       AND client_packages.balance DIV (package_prices.price DIV package_prices.package_length) = 0
            # GROUP BY clients.id
        ';

        // Return the result.
        return $this->db->getAll($sql);
    }

    /**
     * Display all the clients matching the query.
     *
     * @param  $limitFrom
     * @param  $limitTo
     * @param  $query
     * @return array
     */
    public function displayClients($limitFrom, $limitTo, $query)
    {
        return $this->db->getAll(
            '
            SELECT
              id,
              `name`,
              phone,
              email,
              gender,
              is_new AS isNew
            FROM clients
            WHERE `name` LIKE ?s
            OR phone LIKE ?s
            OR email LIKE ?s
            LIMIT ?i, ?i
        ',
            "%$query%",
            "%$query%",
            "%$query%",
            $limitFrom,
            $limitTo
        );
    }

    /**
     * Add a client.
     *
     * @param  $name
     * @param  $phone
     * @param  $email
     * @param  $city
     * @return integer
     */
    public function createClient($name, $phone, $email, $city)
    {
        $sql = '
            INSERT INTO clients (`name`, phone, email, gender, is_new, city)
            VALUES (?s, ?s, ?s, NULL, 1, ?s)
        ';

        // Store the client.
        $this->db->query($sql, $name, $phone, $email, $city);

        // Return the new client's number.
        return (int)$this->db->insertId();
    }

    /**
     * Edit the client.
     *
     * @param  $clientId
     * @param  $name
     * @param  $phone
     * @param  $email
     * @param  $city
     * @return bool
     */
    public function editClient($clientId, $name, $phone, $email, $city)
    {
        $sql = '
            UPDATE clients
            SET
              `name` = coalesce(?s, `name`),
              phone = coalesce(?s, phone),
              email = coalesce(?s, email),
              city = coalesce(?i, city)
            WHERE id = ?i
        ';

        return $this->db->query($sql, $name, $phone, $email, $city, $clientId);
    }

    /**
     * Get all data of the client.
     *
     * @param  $id
     * @return array|FALSE
     */
    public function getClientById($id)
    {
        $sql = '
            SELECT
              id,
              `name`,
              phone,
              email,
              gender,
              is_new AS isNew
            FROM clients
            WHERE id = ?i
        ';

        return $this->db->getRow($sql, $id);
    }

    /**
     * Get the client by phone number.
     *
     * @param  $phone
     * @return array|FALSE
     */
    public function getClientByPhone($phone)
    {
        $sql = '
            SELECT
                id,
                `name`,
                phone,
                email,
                gender,
                is_new AS isNew
            FROM clients
            WHERE phone = ?s
            ';
        return $this->db->getRow($sql, $phone);
    }

    /**
     * Get comments of the user.
     *
     * @param  int $clientId
     * @param  int $limitFrom
     * @param  int $limitTo
     * @return array
     */
    public function clientGetComments($clientId, $limitFrom, $limitTo)
    {
        $sql = '
            SELECT
                client_comments.id,
                client_comments.employee_id AS employeeId,
                client_comments.`comment`,
                client_comments.`date`,
                employees.picture,
                employees.name
            FROM client_comments
            JOIN employees ON client_comments.employee_id = employees.id
            WHERE client_comments.client_id = ?i
            ORDER BY DATE DESC
            LIMIT ?i, ?i
        ';

        return $this->db->getAll($sql, $clientId, $limitFrom, $limitTo);
    }

    /**
     * Push comment to the client's profile.
     *
     * @param  $employeeId
     * @param  $clientId
     * @param  $comment
     * @return FALSE|resource
     */
    public function pushComment($employeeId, $clientId, $comment)
    {
        $sql = '
            INSERT INTO client_comments (client_id, employee_id, `comment`)
            VALUES (?i, ?i, ?s)
        ';

        return $this->db->query($sql, $clientId, $employeeId, $comment);
    }

    /**
     * Get all the packages of the client.
     *
     * @param  $id
     * @return array|false
     */
    public function clientGetPackages($id)
    {
        // SQL query.
        $sql = '
            SELECT
              cp.id,
              cp.package_id     AS packageId,
              cp.price_id       AS priceId,
              cp.comment,
              cp.balance,
              p.id              AS packageId,
              p.name            AS packageName,
              p.period,
              pp.package_length AS packageLength,
              pp.price
            FROM client_packages cp
              JOIN packages p ON cp.package_id = p.id
              JOIN package_prices pp ON cp.price_id = pp.id
            WHERE cp.client_id = ?i
            AND cp.deleted = 0
            ORDER BY package_length ASC
        ';

        // Return the result.
        return $this->db->getAll($sql, $id);
    }

    /**
     * Check if the package is held.
     *
     * @param  $clientPackageId
     * @return bool
     */
    public function isPackageHold($clientPackageId)
    {
        // SQL query.
        $sql = '
            SELECT id
            FROM client_payments
            WHERE client_package_id = ?i
            AND is_confirmed = 0
            AND canceled = 0
            AND action_type != 5
            ORDER BY id DESC
            LIMIT 1
        ';

        // Get the result.
        $result = $this->db->getRow($sql, $clientPackageId);

        // Return the result.
        return (bool)$result['id'];
    }

    /**
     * Get addresses by the user's id.
     *
     * @param  $clientPackageId
     * @return array
     */
    public function getPackageAddresses($clientPackageId)
    {
        // SQL query.
        $sql = '
            SELECT
              id,
              city,
              street,
              building,
              latitude,
              longitude,
              flat,
              entrance,
              `comment`,
              week_day AS weekDay
            FROM client_addresses
            WHERE client_package_id = ?i
        ';

        // Return the result.
        return $this->db->getAll($sql, $clientPackageId);
    }

    /**
     * Change the comment.
     *
     * @param  $clientPackageId
     * @param  $comment
     * @return FALSE|resource
     */
    public function clientsChangePackageComment($clientPackageId, $comment)
    {
        $sql = '
            UPDATE client_packages
            SET comment = ?s WHERE id = ?i
        ';

        return $this->db->query($sql, $comment, $clientPackageId);
    }

    public function clientsShowPackage($id, $clientPackageId, $newPackageId)
    {
        // SQL query.
        $sql = '
            SELECT
              cp.balance                     AS balance,
              newPackage.name                AS newPackageName,
              oldPackage.name                AS oldPackageName,
              oldPackage.id                  AS oldPackageId,
              oldPackagePrice.id             AS packagePriceId,
              oldPackagePrice.price          AS oldPackagePrice,
              oldPackagePrice.package_length AS oldPackageLength
            FROM packages newPackage
              LEFT JOIN client_packages clientPackages ON clientPackages.client_id = ?i
            LEFT JOIN client_packages  cp ON cp.id = ?i
            LEFT JOIN packages        oldPackage ON oldPackage.id = cp.package_id
            LEFT JOIN package_prices  oldPackagePrice ON cp.price_id = oldPackagePrice.id
            WHERE newPackage.id = ?i
        ';

        // Return the result.
        return $this->db->getRow($sql, $id, $clientPackageId, $newPackageId);
    }

    /**
     * Get the client's deliveries.
     *
     * @param  $clientPackageId
     * @param  $dateFrom
     * @param  $dateTo
     * @return array
     */
    public function getDeliveries($clientPackageId = null, $dateFrom = null, $dateTo = null)
    {
        $sql = '
            SELECT
              d.id,
              d.client_package_id AS clientPackageId,
              d.state,
              d.delivery_date     AS deliveryDate,
              d.employee_id       AS employeeId,
              d.address_id        AS addressId,
              d.time_id           AS timeId,
              a.city              AS addressCity,
              a.street            AS addressStreet,
              a.building          AS addressBuilding,
              a.flat              AS addressFlat,
              a.entrance          AS addressEntrance,
              a.comment           AS addressComment,
              t.start_time        As timeStart,
              t.finish_time       As timeEnd
            FROM client_deliveries d
            LEFT JOIN client_addresses a ON a.id = d.address_id
            LEFT JOIN client_delivery_time t ON t.id = d.time_id
            WHERE d.client_package_id = COALESCE(?i, d.client_package_id)
            AND date(delivery_date) BETWEEN COALESCE(?s, CURDATE() - DAYOFMONTH(CURDATE()) + 1)
            AND COALESCE(?s, LAST_DAY(CURDATE()))
            ORDER BY delivery_date ASC
        ';

        return $this->db->getAll($sql, $clientPackageId, $dateFrom, $dateTo);
    }

    public function getLastDeliveryDate($clientPackageId = null, $dateFrom = null)
    {
        $sql = "
            SELECT
              delivery_date     AS deliveryDate
            FROM client_deliveries
            WHERE client_package_id = COALESCE(?i, client_package_id)
            AND date(delivery_date) BETWEEN COALESCE(?s, CURDATE() - DAYOFMONTH(CURDATE()) + 1)
            AND COALESCE('9999-01-01', LAST_DAY(CURDATE()))
            ORDER BY delivery_date DESC LIMIT 0,1
        ";

        return $this->db->getRow($sql, $clientPackageId, $dateFrom);
    }

    /**
     * Set a delivery.
     *
     * @param  $clientPackageId
     * @param  $state
     * @param  $date
     * @param  $employeeId
     * @param  $addressId
     * @param  $timeId
     * @return bool
     */
    public function setDeliveries($clientPackageId, $state, $date, $employeeId, $addressId, $timeId)
    {
        // SQL query.
        $sql = '
            INSERT INTO client_deliveries (client_package_id, state, delivery_date, employee_id, address_id, time_id)
            VALUES (?i, ?i, ?s, ?i, ?i, ?i)
        ';

        // Return the result.
        return $this->db->query($sql, $clientPackageId, $state, $date, $employeeId, $addressId, $timeId);
    }

    /**
     * Get the address by day number.
     *
     * @param  $clientPackageId
     * @param  $dayNumber
     * @return array|FALSE
     */
    public function getAddressByWeekDayNumber($clientPackageId, $dayNumber)
    {
        $sql = '
            SELECT id
            FROM client_addresses
            WHERE client_package_id = ?i
            AND week_day = ?i
        ';

        return $this->db->getRow($sql, $clientPackageId, $dayNumber);
    }

    /**
     * Get the time by day number.
     *
     * @param  $clientPackageId
     * @param  $dayNumber
     * @return array|FALSE
     */
    public function getTimeByWeekDayNumber($clientPackageId, $dayNumber)
    {
        $sql = '
            SELECT id
            FROM client_delivery_time
            WHERE client_package_id = ?i
            AND week_day = ?i
        ';
        return $this->db->getRow($sql, $clientPackageId, $dayNumber);
    }

    /**
     * Delete the deliveries.
     *
     * @param $clientPackageId
     * @param $from
     * @param $to
     */
    public function clientDeleteDelivery($clientPackageId, $from, $to)
    {
        $sql = '
            DELETE FROM client_deliveries
            WHERE client_package_id = ?i
            AND date(delivery_date) BETWEEN COALESCE(?s, delivery_date) AND COALESCE(?s, delivery_date)
        ';
        $this->db->query($sql, $clientPackageId, $from, $to);
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
    public function clientChangeDelivery($id, $state, $date, $addressId, $timeId)
    {
        // SQL query.
        $sql = '
            UPDATE client_deliveries
            SET state       = coalesce(?i, state),
              delivery_date = coalesce(?s, delivery_date),
              address_id    = coalesce(?i, address_id),
              time_id       = coalesce(?i, time_id)
            WHERE id = ?i
        ';

        // Return the result.
        return (bool)$this->db->query($sql, $state, $date, $addressId, $timeId, $id);
    }

    /**
     * Save the address to the database.
     *
     * @param  $clientPackageId
     * @param  $city
     * @param  $street
     * @param  $building
     * @param  $latitude
     * @param  $longitude
     * @param  $flat
     * @param  $entrance
     * @param  $comment
     * @param  $weekDay
     * @return FALSE|inserted address id
     */
    public function clientAddAddress(
        $clientPackageId,
        $city,
        $street,
        $building,
        $latitude,
        $longitude,
        $flat,
        $entrance,
        $comment,
        $weekDay
    ) {
        $sql = '
            INSERT INTO client_addresses
            (client_package_id, city, street, building, latitude, longitude, flat, entrance, `comment`, week_day)
            VALUES (?i, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?i)
        ';

        return
            $this->db->query($sql, $clientPackageId, $city, $street, $building, $latitude, $longitude, $flat, $entrance, $comment, $weekDay);
    }

    public function clientGetLastAddress()
    {
        $sql = '
            SELECT id from client_addresses ORDER BY id DESC LIMIT 0,1
        ';

        return $this->db->getRow($sql);
    }

    public function clientGetLastTime()
    {
        $sql = '
            SELECT id from client_delivery_time ORDER BY id DESC LIMIT 0,1
        ';

        return $this->db->getRow($sql);
    }


    /**
     * Save the address to the database.
     *
     * @param  $clientPackageId
     * @return FALSE|resource
     */
    public function deliveryExclusions($addressId, $deliveryIds)
    {
        $sql = '
            UPDATE client_deliveries SET address_id = ?i WHERE id IN (?s);
        ';

        return $this->db->query($sql, $addressId, $deliveryIds);
    }


    /**
     * Save the address to the database.
     *
     * @param  $clientPackageId
     * @return FALSE|resource
     */
    public function deliveryTimeExclusions($timeId, $deliveryIds)
    {
        $sql = '
            UPDATE client_deliveries SET time_id = ?i WHERE id IN (?s);
        ';

        return $this->db->query($sql, $timeId, $deliveryIds);
    }


    /**
     * Delete the address.
     *
     * @param  $id
     * @return FALSE|resource
     */
    public function clientDeleteAddress($id)
    {
        return $this->db->query("DELETE FROM client_addresses WHERE id = ?i", $id);
    }

    /**
     * Get all the user's addresses.
     *
     * @param  $clientId
     * @return array
     */
    public function clientGetAddresses($clientId)
    {
        // SQL query.
        $sql = '
            SELECT
              id,
              client_package_id AS clientPackageId,
              city,
              street,
              building,
              latitude,
              longitude,
              flat,
              entrance,
              `comment`,
              week_day          AS weekDay
            FROM client_addresses
            WHERE client_package_id = ?i
        ';

        // Return the result.
        return $this->db->getAll($sql, $clientId);
    }

    /**
     * Update the address.
     *
     * @param  $clientPackageId
     * @param  $city
     * @param  $street
     * @param  $building
     * @param  $latitude
     * @param  $longitude
     * @param  $flat
     * @param  $entrance
     * @param  $comment
     * @param  $weekDay
     * @param  $id
     * @return boolean
     */
    public function clientChangeAddress(
        $clientPackageId,
        $city,
        $street,
        $building,
        $latitude,
        $longitude,
        $flat,
        $entrance,
        $comment,
        $weekDay,
        $id
    ) {
        // SQL query.
        $sql = 'UPDATE client_addresses
                SET client_package_id = ?i, city = ?s, street = ?s, building = ?s, latitude = ?s, longitude = ?s,
                flat = ?s, entrance = ?s, `comment` = ?s, week_day = ?i
                WHERE id = ?i';

        // Return the result.
        return $this->db->query(
            $sql,
            $clientPackageId,
            $city,
            $street,
            $building,
            $latitude,
            $longitude,
            $flat,
            $entrance,
            $comment,
            $weekDay,
            $id
        );
    }

    /**
     * Getting all the packages of the user.
     *
     * @return array
     */
    public function clientPackagesList()
    {
        return $this->db->getAll('SELECT id, name FROM packages');
    }

    /**
     * Getting all the user's packages matching the query.
     *
     * @param  $string
     * @return array
     */
    public function clientPackagesSearch($string)
    {
        return $this->db->getAll(
            '
            SELECT id, `name`, period AS deliveryDaysPeriod
            FROM packages
            WHERE concat(name) LIKE ?s
        ',
            "%$string%"
        );
    }


    // HISTORY

    /**
     * Get the client's history from the database.
     *
     * @param  int $clientId  Client's ID.
     * @param  int $query     Search query.
     * @param  int $limitFrom First element's ID.
     * @param  int $limitTo   Number of elements.
     * @return array|false
     */
    public function clientGetHistory($clientId, $query, $limitFrom, $limitTo)
    {
        $sql = '
            SELECT
              client_history.id,
              client_history.client_package_id AS clientPackageId,
              client_history.package_id        AS packageId,
              client_history.employee_id       AS employeeId,
              client_history.amount,
              client_history.payment_type      AS paymentType,
              client_history.action_type       AS actionType,
              client_history.updated_at        AS updatedAt,
              packages.name
            FROM client_history
              JOIN packages
                ON client_history.package_id = packages.id
              LEFT JOIN client_packages
                ON client_history.client_package_id = client_packages.id
              JOIN actions
                ON actions.id = client_history.action_type
            WHERE client_packages.client_id = ?i
                  AND (
                    packages.name LIKE ?s OR
                    amount LIKE ?s OR
                    DATE_FORMAT(client_history.updated_at, "%d %M %Y") LIKE ?s OR
                    actions.name LIKE ?s
                  )
            ORDER BY updated_at DESC
            LIMIT ?i, ?i
        ';

        // Set local names.
        $this->db->query('SET lc_time_names="ru_RU";');

        return $this->db->getAll($sql, $clientId, "%$query%", "%$query%", "%$query%", "%$query%", $limitFrom, $limitTo);
    }

    // END HISTORY


    /**
     * Set new delivery time.
     *
     * @param  $clientPackageId
     * @param  $start
     * @param  $finish
     * @param  $interval
     * @param  $weekDay
     * @return FALSE|resource
     */
    public function clientSetTime($clientPackageId, $start, $finish, $interval, $weekDay)
    {
        // SQL query.
        $sql = 'INSERT INTO client_delivery_time (client_package_id, start_time, finish_time, interval_time, week_day)
                VALUES (?i, ?s, ?s, ?i, ?i)';

        // Return the result.
        return $this->db->query($sql, $clientPackageId, $start, $finish, $interval, $weekDay);
    }

    /**
     * Unset the time.
     *
     * @param  $id
     * @return FALSE|resource
     */
    public function clientUnsetTime($id)
    {
        $sql = 'DELETE FROM client_delivery_time
                WHERE id = ?i';
        return $this->db->query($sql, $id);
    }

    /**
     * Update the time.
     *
     * @param  $start
     * @param  $finish
     * @param  $interval
     * @param  $id
     * @return FALSE|resource
     */
    public function clientChangeTime($start, $finish, $interval, $id)
    {
        $sql = '
            UPDATE client_delivery_time
            SET start_time = ?s, finish_time = ?s, interval_time = ?i
            WHERE id = ?i
        ';

        return $this->db->query($sql, $start, $finish, $interval, $id);
    }

    /**
     * Get the delivery time of the package.
     *
     * @param  $clientPackageId
     * @return array
     */
    public function getPackageTimes($clientPackageId)
    {
        // SQL query.
        $sql = '
            SELECT
              start_time    AS start,
              finish_time   AS finish,
              interval_time AS `interval`,
              week_day      AS weekDay
            FROM client_delivery_time
            WHERE client_package_id = ?i
        ';

        // Return the result.
        return $this->db->getAll($sql, $clientPackageId);
    }


    // CERTIFICATES

    /**
     * Add new certificate.
     *
     * @param  $expiration
     * @param  $comment
     * @param  $discount
     * @param  $type
     * @param  $clientId
     * @param  $number
     * @return FALSE|resource
     */
    public function clientAddCertificate($expiration, $comment, $discount, $clientId, $number)
    {
        $sql = '
            INSERT INTO client_certificates (expiration, `comment`, discount, client_id, `number`)
            VALUES (?s, ?s, ?i, ?i, ?i)
        ';

        return $this->db->query($sql, $expiration, $comment, $discount, $clientId, $number);
    }

    /**
     * Get the certificate.
     *
     * @param  $certificateNumber
     * @return array|FALSE
     */
    public function getCertificate($certificateNumber)
    {
        $sql = '
            SELECT
              client_certificates.id         AS certificateId,
              client_certificates.expiration AS certificateExpiration,
              client_certificates.`comment`  AS certificateComment,
              client_certificates.discount   AS certificateDiscount,
              client_certificates.`number`   AS certificateNumber,
              client_certificates.client_id  AS clientId
            FROM client_certificates
            WHERE `number` = ?i
        ';

        return $this->db->getRow($sql, $certificateNumber);
    }

    /**
     * Get all the certificates.
     *
     * @param  $from
     * @param  $to
     * @param  $query
     * @return array
     */
    public function clientGetAllCertificates($from, $to, $query)
    {
        // SQL query.
        $sql = '
            SELECT
              id,
              expiration,
              `comment`,
              discount,
              client_id AS clientId,
              number
            FROM client_certificates
            WHERE CONCAT(IFNULL(comment, \'\'), discount, number) LIKE ?s
            LIMIT ?i, ?i
        ';

        // Return the result.
        return $this->db->getAll($sql, "%$query%", $from, $to);
    }

    /**
     * Update the certificate.
     *
     * @param  int $clientId
     * @param  int $certificateNumber
     * @return FALSE|resource
     */
    public function activateCertificate($clientId, $certificateNumber)
    {
        // SQL query.
        $sql = '
            INSERT INTO clients_certificates (certificate_id, client_id) VALUES (?i, ?i)
        ';

        // Return the result.
        return $this->db->query($sql, $clientId, $certificateNumber);
    }

    /**
     * @param  int $certificateNumber
     * @param  int $clientId
     * @return array
     */
    public function certificatesClients($certificateNumber, $clientId = null)
    {
        $sql = '
            SELECT *
            FROM clients_certificates
            JOIN client_certificates ON clients_certificates.certificate_id = client_certificates.number
            WHERE clients_certificates.client_id = COALESCE(?i, clients_certificates.client_id)
            AND client_certificates.number = ?i;
        ';

        return $this->db->getAll($sql, $clientId, $certificateNumber);
    }

    // END CERTIFICATES


    // REQUESTS

    /**
     * Add a request.
     *
     * @param  $clientId
     * @param  $question
     * @param  $packageId
     * @return FALSE|resource
     */
    public function addRequest($clientId, $question, $packageId)
    {
        $sql = '
            INSERT INTO client_requests (client_id, question, package_id)
            VALUES (?i, ?s, ?i)
        ';

        return $this->db->query($sql, $clientId, $question, $packageId);
    }

    /**
     * Get the client's requests
     *
     * @param  int    $limitFrom
     * @param  int    $limitTo
     * @param  string $dateFrom
     * @param  string $dateTo
     * @return array
     */
    public function getRequests($limitFrom, $limitTo)
    {
        // SQL query.
        $sql = '
            SELECT
                clients.id,
                clients.`name`,
                clients.phone,
                clients.email,
                client_requests.question,
                client_requests.package_id AS packageId,
                client_requests.processed,
                client_manager_dependance.employee_id as currentEmployeeWorks,
                employees.name AS employeeName,
                employees.picture AS employeePicture
            FROM clients
            JOIN client_requests ON clients.id = client_requests.client_id
            LEFT JOIN client_manager_dependance ON clients.id = client_manager_dependance.client_id
            LEFT JOIN employees ON client_manager_dependance.employee_id = employees.id
            WHERE reason is NULL AND processed is NULL
            GROUP BY clients.id
            LIMIT ?i, ?i
        ';

        $result = $this->db->getAll($sql, $limitFrom, $limitTo);

        // Return the result.
        return $result;
    }

    /**
     * Archive the request.
     *
     * @param  int $requestId
     * @return FALSE|resource
     */
    public function archiveRequest($requestId)
    {
        $sql = '
            UPDATE client_requests SET archived = 1
            WHERE id = ?i
        ';

        return $this->db->query($sql, $requestId);
    }

    /**
     * Process the request.
     *
     * @param  int    $requestId
     * @param  int    $action
     * @param  string $comment
     * @param  int    $employeeId
     * @return boolean
     */
    public function processRequest($clientId, $action, $comment, $employeeId, $reason)
    {
        $sql = '
            UPDATE client_requests
            SET processed = 1, `comment` = ?s, employee_id = ?i, reason = ?i
            WHERE client_id = ?i AND processed is NULL
        ';

        return $this->db->query($sql, $comment, $employeeId, $reason, $clientId);
    }


    /**
     * Process the attention.
     *
     * @param  int    $requestId
     * @param  int    $action
     * @param  string $comment
     * @param  int    $employeeId
     * @return boolean
     */
    public function processAttention($clientId, $reason, $employeeId)
    {
        
        $sql = 'INSERT INTO client_refuses (client_id, reason_id, employee_id) VALUES (?i, ?i, ?i)';

        $sql2 = '
            UPDATE clients
            SET refuse_reason_id = ?i
            WHERE id = ?i
        ';

        $this->db->query($sql2, $reason, $clientId);
        return $this->db->query($sql, $clientId, $reason, $employeeId);
    }

    public function processSuccess($clientId)
    {
        $sql = "UPDATE client_requests
            SET processed = 1
            WHERE client_id = ?i";

        $this->pushSocket(array('category' => 'newRequests'));

        $result = $this->db->query($sql, $clientId);

        return $result;
    }

    public function showReasons()
    {
        $sql = '
            SELECT reasons.name, reasons.id FROM reasons WHERE deleted = 0
        ';

        return $this->db->getAll($sql);
    }

    public function addReason($reason)
    {
        $sql = 'INSERT INTO reasons (name) VALUES (?s)';

        return $this->db->query($sql, $reason);
    }


    public function removeReason($reasonId)
    {
        $sql = 'UPDATE reasons SET deleted = 1 WHERE id = ?i';

        return $this->db->query($sql, $reasonId);
    }

    // END REQUESTS


    // REMINDERS

    /**
     * Add a reminder.
     *
     * @param  int    $requestId
     * @param  string $date
     * @return FALSE|resource
     */
    public function addReminder($requestId, $date)
    {
        $sql = '
            INSERT INTO reminders (request_id, `date`)
            VALUES (?i, ?s)
        ';

        return $this->db->query($sql, $requestId, $date);
    }

    /**
     * Get all the deferred requests.
     *
     * @return array
     */
    public function getReminders()
    {
        $sql = '
            SELECT
              clients.id,
              `name`,
              phone,
              email,
              gender,
              is_new AS isNew,
              reminders.date
            FROM clients
              JOIN client_requests ON client_requests.client_id = clients.id
              JOIN reminders ON reminders.request_id = client_requests.id
            WHERE client_requests.processed IS NULL
            AND client_requests.archived = 0
            AND reminders.date < NOW()
            ORDER BY date DESC
            LIMIT 0, 1
        ';

        return $this->db->getAll($sql);
    }

    /**
     * Get all the reminders attached to the client.
     *
     * @param  int $reminderId
     * @return array
     */
    public function getRemindersByRequestId($reminderId)
    {
        $sql = '
            SELECT *
            FROM reminders
            WHERE request_id = ?i
        ';

        return $this->db->getAll($sql, $reminderId);
    }

    // END REMINDERS


    /**
     * Confirm the payment.
     *
     * @param  int $paymentId
     * @param  int $paymentType
     * @return boolean
     */
    public function clientConfirmPayment($paymentId, $paymentType)
    {
        // SQL query.
        $sql = '
            UPDATE client_payments
            SET is_confirmed = 1, payment_type = coalesce(?i, payment_type)
            WHERE id = ?i AND
            is_confirmed = 0
        ';

        // Return the result.
        return (bool)$this->db->query($sql, $paymentType, $paymentId);
    }

    /**
     * Confirm the package.
     *
     * @param  $clientPackageId
     * @return boolean
     */
    public function confirmPackage($clientPackageId)
    {
        // SQL query.
        $sql = '
            UPDATE client_packages
            SET confirmed = 1
            WHERE id = ?i
        ';

        // Return the result.
        return (bool)$this->db->query($sql, $clientPackageId);
    }

    /**
     * Get the payment.
     *
     * @param  $paymentId
     * @return array|FALSE
     */
    public function clientGetPayment($paymentId)
    {
        $sql = '
            SELECT
                id,
                client_package_id AS clientPackageId,
                package_id AS packageId,
                employee_id AS employeeId,
                amount,
                payment_type AS paymentType,
                action_type AS actionType,
                is_confirmed AS isConfirmed
            FROM client_payments
            WHERE id = ?i
        ';
        return $this->db->getRow($sql, $paymentId);
    }

    /**
     * Check if the package is to be deleted.
     *
     * @param  $clientPackageId
     * @return array|FALSE
     */
    public function clientCheckDelete($clientPackageId)
    {
        // SQL query.
        $sql = '
            SELECT action_type AS actionType
            FROM client_payments
            WHERE client_package_id = ?i
            AND action_type = 2
            AND is_confirmed = 0
        ';

        // Get the row from the database.
        return $this->db->getRow($sql, $clientPackageId);
    }

    /**
     * Get all the payments matching the query.
     *
     * @param  $dateFrom
     * @param  $dateTo
     * @param  int       $isConfirmed
     * @param  $query
     * @param  $limitFrom
     * @param  $limitTo
     * @return array
     */
    public function clientGetPayments($dateFrom, $dateTo, $query, $limitFrom, $limitTo)
    {
        $sql = "
            SELECT
              client_payments.id                AS paymentId,
              client_payments.client_package_id AS clientPackageId,
              client_payments.amount,
              client_payments.payment_type      AS paymentType,
              client_payments.created_at        AS createdAt,
              clients.id                        AS clientId,
              clients.name                      AS clientName,
              clients.phone,
              clients.email,
              packages.name                     AS packageName
            FROM client_payments
              JOIN client_packages ON client_payments.client_package_id = client_packages.id
              JOIN clients ON client_packages.client_id = clients.id
              JOIN packages ON client_packages.package_id = packages.id
              LEFT JOIN blocks ON clients.id = blocks.client_id
            WHERE is_confirmed = 0
                  AND date(updated_at) BETWEEN coalesce(?s, '1000-01-01') AND coalesce(?s, '9999-12-31')
                  AND (
                    clients.name LIKE ?s OR
                    clients.phone LIKE ?s OR
                    clients.email LIKE ?s OR
                    client_payments.amount LIKE ?s OR
                    packages.name LIKE ?s
                  )
                  AND client_payments.payment_type != 3
                  AND client_payments.canceled = 0
                  AND clients.id NOT IN (SELECT client_id
                                         FROM blocks
                                         WHERE id IN (SELECT MAX(id)
                                                      FROM blocks
                                                      GROUP BY client_id) AND type = 1
                                         ORDER BY client_id)
            GROUP BY client_payments.id
            LIMIT ?i, ?i
        ";

        return $this->db->getAll(
            $sql,
            $dateFrom,
            $dateTo,
            "%$query%",
            "%$query%",
            "%$query%",
            "%$query%",
            "%$query%",
            $limitFrom,
            $limitTo
        );
    }

    /**
     * Get action_type.
     *
     * @param  $clientPackageId
     * @return array|FALSE
     */
    public function getPackageAction($clientPackageId)
    {
        // SQL query.
        $sql = '
            SELECT
              action_type AS actionType,
              updated_at  AS updatedAt
            FROM client_payments
            WHERE client_package_id = ?i
            AND is_confirmed = 0
            AND canceled = 0
        ';

        // Return the result.
        return $this->db->getRow($sql, $clientPackageId);
    }

    /**
     * Add a debt.
     *
     * @param  $clientPackageId
     * @param  $amount
     * @param  $paymentId
     * @param  $type
     * @return boolean
     */
    public function clientAddDebt($clientPackageId, $amount, $paymentId, $type)
    {
        $sql = '
            INSERT INTO client_debts (client_package_id, amount, payment_id, type)
            VALUES (?i, ?i, ?i, ?i)
        ';

        return $this->db->query($sql, $clientPackageId, $amount, $paymentId, $type);
    }

    /**
     * Change the client's debt.
     *
     * @param $debtId
     * @param $amount
     */
    public function clientChangeDebt($debtId, $amount)
    {
        // SQL query.
        $sql = '
            UPDATE client_debts
            SET amount = ?i
            WHERE id = ?i
        ';

        // Update the data.
        $this->db->query($sql, $amount, $debtId);
    }

    /**
     * Get the debt by the client's package.
     *
     * @param  $clientPackageId
     * @return array|FALSE
     */
    public function clientGetDebtByClientPackage($clientPackageId)
    {
        $sql = '
            SELECT id, amount
            FROM client_debts
            WHERE client_package_id = ?i
        ';
        return $this->db->getRow($sql, $clientPackageId);
    }

    /**
     * Check if the user paid a debt that is not yet confirmed.
     *
     * @param  $clientId
     * @return array|FALSE
     */
    public function isDebtHold($clientId)
    {
        // SQL query.
        $sql = '
            SELECT client_payments.id
            FROM client_payments
              JOIN client_packages ON client_payments.client_package_id = client_packages.id
            WHERE client_packages.client_id = ?i
                  AND client_payments.action_type = 5
                  AND client_payments.is_confirmed = 0
                  AND client_payments.canceled = 0
        ';

        // Return the result.
        return boolval($this->db->getAll($sql, $clientId));
    }

    /**
     * Get the debt by its id.
     *
     * @param  $debtId
     * @return array|FALSE
     */
    public function clientGetDebtById($debtId)
    {
        // SQL query.
        $sql = '
            SELECT
              client_package_id AS clientPackageId,
              amount,
              payment_id        AS paymentId
            FROM client_debts
            WHERE id = ?i
        ';

        // Return the result.
        return $this->db->getRow($sql, $debtId);
    }

    /**
     * Count the user's debts.
     *
     * @param  $clientId
     * @return array|FALSE
     */
    public function clientCountDebtSum($clientId)
    {
        $sql = '
            SELECT SUM(client_debts.amount) AS debtSum
            FROM client_debts
              JOIN client_packages ON client_debts.client_package_id = client_packages.id
            WHERE client_packages.client_id = ?i
        ';
        return $this->db->getRow($sql, $clientId);
    }

    /**
     * Get the package's debt.
     *
     * @param  $clientPackageId
     * @return array|FALSE
     */
    public function clientGetPackageDebt($clientPackageId)
    {
        $sql = '
            SELECT amount AS debt
            FROM client_debts
            WHERE client_package_id = ?i
        ';

        return $this->db->getRow($sql, $clientPackageId);
    }

    /**
     * Get the client's debts.
     *
     * @param  $clientId
     * @return array
     */
    public function getDebtsByClient($clientId)
    {
        $sql = '
            SELECT
              client_debts.id,
              client_debts.client_package_id AS clientPackageId,
              client_debts.amount,
              client_debts.payment_id        AS paymentId
            FROM client_debts
              JOIN client_payments ON client_payments.id = client_debts.payment_id
              JOIN client_packages ON client_packages.id = client_payments.client_package_id
              JOIN clients ON client_packages.client_id = clients.id
            WHERE clients.id = ?i
                  AND client_debts.amount > 0
        ';

        return $this->db->getAll($sql, $clientId);
    }

    /**
     * Add a new package.
     *
     * @param  int $clientId
     * @param  int $packageId
     * @param  int $priceId
     * @param  int $confirmed
     * @return int
     */
    public function clientAddPackage($clientId, $packageId, $priceId, $confirmed = 1)
    {
        // Attach the package to the client.
        $sql = '
            INSERT INTO client_packages (client_id, package_id, price_id, confirmed)
            VALUES (?i, ?i, ?i, ?i);
        ';

        $this->db->query($sql, $clientId, $packageId, $priceId, $confirmed);

        return $this->db->insertId();
    }

    /**
     * Change the package.
     *
     * @param  $clientPackageId
     * @param  $packageId
     * @param  $priceId
     * @return boolean
     */
    public function changePackage($clientPackageId, $packageId, $priceId)
    {
        $sql = '
            UPDATE client_packages
            SET package_id = ?i, price_id = ?i
            WHERE id = ?i
        ';

        return $this->db->query($sql, $packageId, $priceId, $clientPackageId);
    }

    /**
     * Delete the client's package.
     *
     * @param  int $clientPackageId
     * @return FALSE|resource
     */
    public function clientDeletePackage($clientPackageId)
    {
        $sql = '
            UPDATE client_packages
            SET deleted = 1
            WHERE id = ?i
        ';

        return $this->db->query($sql, $clientPackageId);
    }

    /// END OF CLIENTS SECTION ///

    /// HISTORY SECTION ///

    /**
     * Writing changes to the log table.
     *
     * @param  $clientPackageId
     * @param  $packageId
     * @param  $employeeId
     * @param  $amount
     * @param  $paymentType
     * @param  $actionType
     * @return bool
     */
    public function history($clientPackageId, $packageId, $employeeId, $amount, $paymentType, $actionType)
    {
        return $this->db->query(
            '
            INSERT INTO client_history (client_package_id, package_id, employee_id, amount, payment_type, action_type)
            VALUES (?i, ?i, ?i, ?i, ?i, ?i)
        ',
            $clientPackageId,
            $packageId,
            $employeeId,
            $amount,
            $paymentType,
            $actionType
        );
    }

    /// END OF HISTORY SECTION ///

    /// HISTORY SECTION ///

    /**
     * Writing changes to the log table.
     *
     * @param  $clientPackageId
     * @param  $packageId
     * @param  $employeeId
     * @param  $amount
     * @param  $paymentType
     * @param  $actionType
     * @return bool
     */
    public function payment($clientPackageId, $packageId, $employeeId, $amount, $paymentType, $actionType)
    {
        $confirmation = $amount ? 0 : 1;

        $result = $this->db->query(
            '
            INSERT INTO client_payments (client_package_id, package_id, employee_id, amount, payment_type, action_type, is_confirmed)
            VALUES (?i, ?i, ?i, ?i, ?i, ?i, ?i)
        ',
            $clientPackageId,
            $packageId,
            $employeeId,
            $amount,
            $paymentType,
            $actionType,
            $confirmation
        );

        $this->pushSocket(array('category' => 'dashboardIncome'));

        return $result;
    }

    /**
     * Delete the record from the log table.
     *
     * @param  $clientPackageId
     * @return bool|FALSE|resource
     */
    public function paymentCancel($clientPackageId)
    {
        $this->pushSocket(array('category' => 'dashboardIncome'));
        $result = $this->db->query(
            '
            UPDATE client_payments
            SET canceled = 1
            WHERE client_package_id = ?i
            AND is_confirmed = 0
        ',
            $clientPackageId
        );

        return $result;
    }

    /// END OF HISTORY SECTION ///

    /// PACKAGES SECTION ///

    /**
     * Get the package's prices by its id.
     *
     * @param  $packageId
     * @return array
     */
    public function packagesGetPricesById($packageId)
    {
        return $this->db->getAll(
            '
            SELECT id, package_length AS packageLength, price
            FROM package_prices
            WHERE package_id = ?i
            ORDER BY package_length
        ',
            $packageId
        );
    }

    /**
     * Get the particular package's price.
     *
     * @param  $packageId
     * @return array|FALSE
     */
    public function packageGetPriceById($packageId)
    {
        return $this->db->getRow(
            '
            SELECT id, package_length AS packageLength, price
            FROM package_prices
            WHERE id = ?i
        ',
            $packageId
        );
    }

    /**
     * Getting all the packages.
     *
     * @return array
     */
    public function packagesList()
    {
        return $this->db->getAll(
            '
            SELECT id, name
            FROM packages
        '
        );
    }

    /**
     * Getting all the packages matching the query.
     *
     * @param  $string
     * @return array
     */
    public function packagesSearch($string)
    {
        return $this->db->getAll(
            '
            SELECT id, name, period AS deliveryDaysPeriod
            FROM packages
            WHERE concat(name)
            LIKE ?s
        ',
            "%$string%"
        );
    }

    /**
     * Get period of the package.
     *
     * @param  $id
     * @return mixed
     */
    public function packageGetPeriod($id)
    {
        // SQL query.
        $sql = '
            SELECT period AS deliveryDaysPeriod
            FROM packages
            WHERE id = ?i
        ';

        // Return the result.
        return $this->db->getCol($sql, $id)[0];
    }

    /**
     * Get the particular package of a client.
     *
     * @param  $id
     * @return array|FALSE
     */
    public function packageGetClientPackage($id)
    {
        $sql = '
            SELECT
                id,
                client_id AS clientId,
                package_id AS packageId,
                price_id AS priceId,
                balance
            FROM client_packages
            WHERE id = ?i';
        return $this->db->getRow($sql, $id);
    }

    /// END OF PACKAGES SECTION ///


    // PERMISSIONS

    public function getPermissions($procedure)
    {
        $sql = '
            SELECT employee_type
            FROM permissions
              JOIN procedures ON procedures.id = permissions.procedure_id
            WHERE procedures.name = ?s
        ';

        return $this->db->getRow($sql, $procedure);
    }

    // END PERMISSIONS


    /**
     * Withdraw money.
     *
     * @param  $clientPackageId
     * @return boolean
     */
    public function withdrawBalance($clientPackageId)
    {
        // SQL query.
        $sql = '
            UPDATE client_packages
              JOIN packages ON client_packages.package_id = packages.id
              JOIN package_prices ON client_packages.price_id = package_prices.id
            SET balance = balance - package_prices.price / package_prices.package_length
            WHERE client_packages.id = ?i
        ';

        // Return the result.
        return $this->db->query($sql, $clientPackageId);
    }

    /**
     * Add money to the balance.
     *
     * @param  $amount
     * @param  $clientPackageId
     * @return boolean
     */
    public function fulfillBalance($amount, $clientPackageId)
    {
        // SQL query.
        $sql = '
            UPDATE client_packages
            SET balance = balance + ?i
            WHERE id = ?i
        ';

        // Return the result.
        return $this->db->query($sql, $amount, $clientPackageId);
    }

    /**
     * Block the user.
     *
     * @param  $clientId
     * @param  $type
     * @param  $comment
     * @return boolean
     */
    public function blockClient($clientId, $type, $comment, $employeeId)
    {
        // SQL query.
        $sql = '
            INSERT INTO blocks (client_id, type, comment, employeeId) VALUES (?i, ?i, ?s, ?i)
        ';

        // Return the result.
        return $this->db->query($sql, $clientId, $type, $comment, $employeeId);
    }

    /**
     * Unblock user.
     *
     * @param  $clientId
     * @param  $type
     * @param  $comment
     * @return boolean
     */
    public function unblockClient($clientId)
    {
        // SQL query.
        $sql = '
            DELETE FROM blocks WHERE client_id = ?i
        ';

        // Return the result.
        return $this->db->query($sql, $clientId);
    }


    /**
     * Check if the client is blocked.
     *
     * @param  int $clientId
     * @return boolean
     */
    public function checkIfBlocked($clientId)
    {
        $sql = '
            SELECT
              b.client_id AS clientId,
              b.type,
              b.employeeId,
              b.comment,
              e.name AS employeeName,
              e.picture as employeePicture
            FROM blocks b
            LEFT JOIN employees e ON e.id = b.employeeId
            WHERE client_id = COALESCE(?i, client_id)
            ORDER BY b.id DESC
            LIMIT 1
        ';

        return $this->db->getRow($sql, $clientId);
    }

    /**
     * Get my stats.
     *
     * @return array|FALSE
     */
    public function myStats()
    {
        // SQL query.
        $sql = '
            SELECT
              employees.name,
              employees.picture,
              SUM(DISTINCT IF(client_payments.canceled = 0 AND client_payments.is_confirmed = 1, client_payments.amount, NULL)) AS sum,
              (COUNT(DISTINCT IF(client_requests.reason IS NOT NULL, 1, NULL)) + COUNT(DISTINCT IF(client_refuses.id IS NOT NULL, 1, NULL))) AS canceled,
              COUNT(DISTINCT IF(client_payments.canceled = 0 AND client_payments.is_confirmed = 1, client_payments.id, NULL))   AS success
            FROM employees
              JOIN client_payments ON client_payments.employee_id = employees.id
              LEFT JOIN client_requests ON client_requests.employee_id = employees.id
              LEFT JOIN client_refuses ON client_refuses.employee_id = employees.id
            WHERE
              YEAR(client_payments.updated_at) = YEAR(CURDATE())
              AND MONTH(client_payments.updated_at) = MONTH(CURDATE())
              AND DATE(updated_at) <= DATE(CURDATE())
            GROUP BY employees.id
        ';

        // Return the result.
        return $this->db->getAll($sql);
    }

    /**
     * Get the client's bonuses.
     *
     * @param  int $clientId
     * @return array
     */
    public function getBonuses($clientId)
    {
        $sql = '
            SELECT
              id,
              amount,
              expiration
            FROM bonuses
            WHERE client_id = ?i
            AND (expiration > CURDATE() OR expiration IS NULL)
            AND amount > 0
            ORDER BY expiration DESC
        ';

        return $this->db->getAll($sql, $clientId);
    }

    /**
     * Get the client's bonuses.
     *
     * @param  int $clientId
     * @return mixed
     */
    public function clientBonusesTotal($clientId)
    {
        $sql = '
            SELECT SUM(amount) AS amount
            FROM bonuses
            WHERE client_id = ?i
            ORDER BY expiration DESC
        ';

        return $this->db->getRow($sql, $clientId)['amount'];
    }

    /**
     * Withdraw the client's bonuses.
     *
     * @param  int $bonusId
     * @param  int $amount
     * @return boolean
     */
    public function withdrawBonuses($bonusId, $amount)
    {
        $sql = '
            UPDATE bonuses
            SET amount = ?i
            WHERE id = ?i
        ';

        return $this->db->query($sql, $amount, $bonusId);
    }

    /**
     * Increase the client's bonuses.
     *
     * @param  int $amount
     * @param  int $clientId
     * @return boolean
     */
    public function increaseBonus($clientId, $amount)
    {
        $sql = '
            UPDATE bonuses
            SET amount = amount + ?i
            WHERE client_id = ?i
            AND expiration IS NULL
        ';

        return $this->db->query($sql, $amount, $clientId);
    }

    /**
     * Create a bonus.
     *
     * @param  int    $clientId
     * @param  int    $amount
     * @param  string $expiration
     * @param  string $comment
     * @return FALSE|resource
     */
    public function createBonus($clientId, $amount, $expiration = null, $comment = null)
    {
        $sql = '
            INSERT INTO bonuses (client_id, amount, expiration, `comment`)
            VALUES (?i, ?i, ?s, ?s)
        ';

        return $this->db->query($sql, $clientId, $amount, $expiration, $comment);
    }

    /**
     * Get the employee.
     *
     * @param  int $employeeId - The employee's ID.
     * @return array|FALSE
     */
    public function getEmployee($employeeId)
    {
        $sql = '
            SELECT
              employees.id AS employeeId,
              type,
              picture,
              employees.name AS employeeName,
              employees_types.name AS typeName
            FROM employees
            JOIN employees_types ON employees.type = employees_types.id
            WHERE employees.id = ?i
        ';

        return $this->db->getRow($sql, $employeeId);
    }


    // DASHBOARD

    /**
     * Get payments for the dashboard.
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @return array
     */
    public function dashboardPayments($dateFrom, $dateTo)
    {
        $sql = '
            SELECT
              SUM(amount)                      AS total,
              DATE(client_payments.created_at) AS `date`
            FROM client_payments
              JOIN client_packages ON client_payments.client_package_id = client_packages.id
              JOIN clients ON client_packages.client_id = clients.id
              JOIN packages ON client_packages.package_id = packages.id
              LEFT JOIN blocks ON clients.id = blocks.client_id
            WHERE is_confirmed = 1
                  AND date(updated_at) >= ?s AND DATE(updated_at) <= ?s
                  AND client_payments.canceled = 0
                  AND clients.id NOT IN (SELECT client_id
                                         FROM blocks
                                         WHERE id IN (SELECT MAX(id)
                                                      FROM blocks
                                                      GROUP BY client_id) AND type = 1
                                         ORDER BY client_id)
            GROUP BY DAY(`date`)
        ';

        return $this->db->getAll($sql, $dateFrom, $dateTo);
    }

    /**
     * Requests counter.
     *
     * @param  $dateFrom
     * @param  $dateTo
     * @param  $type
     * @return FALSE|array
     */
    public function dashboardRequests($dateFrom, $dateTo)
    {
        $sql = '
            SELECT
                COUNT(id) AS `count`,
                DATE(created_at) AS `date`
                FROM client_requests
                WHERE date(created_at) BETWEEN coalesce(?s, CURDATE() - DAYOFMONTH(NOW()) + 1) AND coalesce(?s,
                  LAST_DAY(CURDATE()))
                AND archived = 0
                GROUP BY `date`
        ';

        return $this->db->getAll($sql, $dateFrom, $dateTo);
    }

    /**
     * Debts for the dashboard.
     *
     * @param  int $dateFrom
     * @param  int $dateTo
     * @return mixed
     */
    public function dashboardDebt($dateFrom, $dateTo)
    {
        $sql = '
            SELECT SUM(amount) AS sum
            FROM client_debts
            WHERE date(created_at) BETWEEN coalesce(?s, CURDATE() - DAYOFMONTH(NOW()) + 1) AND coalesce(?s,
                    LAST_DAY(CURDATE()))
        ';

        return $this->db->getRow($sql, $dateFrom, $dateTo)['sum'];
    }

    public function dashboardWaitingForPayment($dateFrom, $dateTo)
    {
        $sql = '
            SELECT SUM(amount) AS sum
            FROM client_payments
            WHERE is_confirmed = 0 AND canceled = 0 AND date(created_at) BETWEEN coalesce(?s, CURDATE() - DAYOFMONTH(NOW()) + 1) AND coalesce(?s,
                    LAST_DAY(CURDATE()))
        ';

        return $this->db->getRow($sql, $dateFrom, $dateTo)['sum'];
    }

    /**
     * Turnover.
     *
     * @param  int    $paymentType
     * @param  string $dateFrom
     * @param  string $dateTo
     * @return mixed
     */
    public function dashboardTurnover($paymentType, $dateFrom, $dateTo)
    {
        $sql = '
            SELECT SUM(amount) AS sum
            FROM client_payments
            WHERE date(updated_at) BETWEEN coalesce(?s, CURDATE() - DAYOFMONTH(NOW()) + 1) AND coalesce(?s,
                    LAST_DAY(CURDATE()))
            AND payment_type = ?i
            AND canceled = 0
            AND is_confirmed = 1
        ';

        return $this->db->getRow($sql, $dateFrom, $dateTo, $paymentType)['sum'];
    }

    public function dashboardCertificates($dateFrom, $dateTo)
    {
        $sql = '
            SELECT SUM(client_certificates.discount) as sum
            FROM client_certificates
            JOIN clients_certificates ON client_certificates.number = clients_certificates.certificate_id
            WHERE date(created_at) BETWEEN coalesce(?s, CURDATE() - DAYOFMONTH(NOW()) + 1) AND coalesce(?s,
                    LAST_DAY(CURDATE()))
        ';

        return $this->db->getRow($sql, $dateFrom." 00:00:00", $dateTo." 00:00:00")['sum'];
    }

    public function dashboardRejected($dateFrom, $dateTo)
    {
        $sql = '
            SELECT
            reasons.id,
            reasons.name,
            COUNT(DISTINCT client_refuses.id) + COUNT(DISTINCT client_requests.id) AS refuse
            FROM reasons
            LEFT JOIN client_refuses ON client_refuses.reason_id = reasons.id AND date(client_refuses.created_at) BETWEEN coalesce(?s, CURDATE() - DAYOFMONTH(NOW()) + 1) AND coalesce(?s,
                    LAST_DAY(CURDATE()))
            LEFT JOIN client_requests ON client_requests.reason = reasons.id AND date(client_requests.created_at) BETWEEN coalesce(?s, CURDATE() - DAYOFMONTH(NOW()) + 1) AND coalesce(?s,
                    LAST_DAY(CURDATE()))
            WHERE reasons.deleted = 0
            GROUP BY reasons.id 
        ';

        return $this->db->getAll($sql, $dateFrom, $dateTo, $dateFrom, $dateTo);
    }

    // END DASHBOARD


    // CITIES

    /**
     * Get cities.
     *
     * @return array
     */
    public function getCities()
    {
        $sql = '
            SELECT
              id,
              name,
              timezone
            FROM cities
        ';

        return $this->db->getAll($sql);
    }

    /**
     * Get the client's city.
     *
     * @param  int $clientId - The client's ID.
     * @return array|false
     */
    public function getCityByClientId($clientId)
    {
        $sql = '
            SELECT
              cities.id,
              cities.name,
              cities.timezone
            FROM cities
            JOIN clients ON cities.id = clients.city
            WHERE clients.id = ?i
        ';

        return $this->db->getRow($sql, $clientId);
    }

    // END CITIES

    public function checkClientIsBusy($clientId)
    {
        $sql = '
            SELECT
              dependant.id,
              dependant.employee_id,
              employees.name,
              employees.picture
            FROM client_manager_dependance AS dependant
            JOIN employees ON employee_id = employees.id
            WHERE client_id = ?i
        ';

        return $this->db->getRow($sql, $clientId);
    }

    public function setClientBusy($clientId, $employeeId)
    {
        $sql = '
            INSERT INTO client_manager_dependance (employee_id, client_id)
            VALUES (?i, ?i)
        ';

        // Store the client.
        $this->db->query($sql, $employeeId, $clientId);
    }

    public function removeClientBusiness($clientId)
    {
        $sql = '
            DELETE FROM client_manager_dependance
            WHERE client_id = ?i
        ';

        $this->db->query($sql, $clientId);
    }

    public function addClientRefund($clientPackageId, $employeeId, $comment)
    {
        $sql = ' INSERT INTO client_refunds 
                 (employee_id, client_package_id, comment, is_confirmed, is_canceled)
                 VALUES (?i, ?i, ?s, 0, 0)';

        return $this->db->query($sql, $employeeId, $clientPackageId, $comment);
    }

    public function showRefunds()
    {
        $sql = 'SELECT 
                    client_refunds.id,
                    client_refunds.employee_id, 
                    client_refunds.client_package_id,
                    client_refunds.comment, 
                    client_refunds.is_confirmed, 
                    client_refunds.is_canceled,

                    employees.name AS employeeName,
                    employees.picture AS employeePicture,

                    clients.id  AS clientId,
                    clients.name AS clientName,
                    clients.phone AS clientPhone,
                    clients.email AS clientEmail,

                    client_packages.balance AS balance,

                    packages.name AS packageName

                FROM client_refunds
                JOIN employees ON employees.id = client_refunds.employee_id
                JOIN client_packages ON client_refunds.client_package_id = client_packages.id
                JOIN clients ON clients.id = client_packages.client_id
                JOIN packages ON client_packages.package_id = packages.id
                WHERE client_refunds.is_canceled = 0 AND client_refunds.is_confirmed = 0
        ';

        return $this->db->getAll($sql);
    }

    public function getClientPackageIdByRefundId($refundId)
    {
        $sql = 'SELECT client_package_id from client_refunds WHERE id = ?i';

        return $this->db->getRow($sql, $refundId);
    }

    public function confirmRefund($refundId)
    {
        $sql = 'UPDATE client_refunds SET is_confirmed = 1 WHERE id = ?i';

        return $this->db->query($sql, $refundId);
    }

    public function cancelRefund($refundId)
    {
        $sql = 'UPDATE client_refunds SET is_canceled = 1 WHERE id = ?i';

        return $this->db->query($sql, $refundId);
    }

    // agents start
    public function registerAgent($name, $email, $phone, $password, $profession, $picture, $inviteHash)
    {
        $sql = ' INSERT INTO agents 
                 (name, email, phone, password, profession, picture, inviteHash)
                 VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s)';

        return $this->db->query($sql, $name, $email, $phone, $password, $profession, $picture, $inviteHash);
    }

    /**
     * @param string $phone
     * @param string $password
     * @return array
     */
    public function loginAgent(string $phone, string $password)
    {
        $sql = '
            SELECT
              id,
              `name`,
              profession,
              picture,
              balance,
              inviteHash
            FROM agents
            WHERE phone = ?s AND PASSWORD = ?s
        ';

        return $this->db->getAll($sql, $phone, $password);
    }

    public function getActionHistory(int $id)
    {
        $sql = '
            SELECT
                agent_history.id,
                agent_history.type,
                agent_history.balance_change_value,
                agent_history.agent_id,
                agent_history.client_id,
                agent_history.client_package_id,
                agent_history.invited_agent_id,
                agent_history.date
            FROM
                agents
                    LEFT JOIN
                agent_history ON agents.id = agent_history.agent_id
            WHERE
                agents.id = ?i
            ORDER BY DATE DESC
            LIMIT 20
        ';

        return $this->db->getAll($sql, $id);
    }

    public function getSpecials()
    {
        $sql = '
            SELECT
               agent_specials.id,
               agent_specials.name,
               agent_specials.picture,
               agent_specials.description,
               agent_specials.is_draft,
               agent_specials.created_at
            FROM agent_specials
            ORDER BY created_at DESC
            LIMIT 20
        ';

        return $this->db->getAll($sql);
    }
}
