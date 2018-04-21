<?php

namespace App;

use \Core\API;
use DateTime;

/**
 * Class Sale
 *
 * @package App
 */
class Sale extends API
{
    /**
     * Client constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Calculate statistics.
     *
     * @param  string $from
     * @param  string $to
     * @param  int    $interval
     * @return array
     */
    function calculate($from, $to, $interval)
    {
        $paymentsDates = [];
        $requestDates = [];

        $payments = $this->pd->dashboardPayments($from, $to);
        $requests = $this->pd->dashboardRequests($from, $to);



        // Accepted mapping
        foreach ($requests as $item) {
            $requestDates[$item['date']] = (int)$item['count'];
        }

        // Payments mapping
        foreach ($payments as $payment) {
            $paymentsDates[$payment['date']] = (int)$payment['total'];
        }

        // Payments mapping
        foreach ($payments as $payment) {
            $paymentsDates[$payment['date']] = (int)$payment['total'];
        }

        for ($i = 0; $i < $interval; $i++) {
            $date = date('Y-m-d', strtotime("+{$i} day", strtotime($from)));
            if (!$this->dateEntries($date, $payments)) {
                $paymentsDates[$date] = 0;
            }
            if (!$this->dateEntries($date, $requests)) {
                $requestDates[$date] = 0;
            }
        }

        // Requests mapping

        $result = array(
            'requests' => array(
                'dates' => $requestDates
            ),
            'debt' => (int)$this->pd->dashboardDebt($from, $to),
            'waiting' => (int)$this->pd->dashboardWaitingForPayment($from, $to),
            'payments' => array(
                'dates' => $paymentsDates,
                'turnover' => array(
                    'cash' => (int)$this->pd->dashboardTurnover(1, $from, $to),
                    'card' => (int)$this->pd->dashboardTurnover(2, $from, $to),
                    'online' => (int)$this->pd->dashboardTurnover(3, $from, $to)
                ),
            ),
            'certificates' => (int)$this->pd->dashboardCertificates($from, $to),
            'rejected' => $this->pd->dashboardRejected($from, $to)
        );

        return $result;
    }

    // Check if there were sales on this date.
    public function dateEntries($needle, $haystack)
    {
        foreach ($haystack as $item) {
            if ($item['date'] == $needle) {
                return true;
            };
        }
        return false;
    }

    /**
     * Get the dashboard data.
     *
     * @param  string $dateFrom
     * @param  string $dateTo
     * @return array|string
     */
    public function getDashboard($dateFrom, $dateTo)
    {
        $dateFrom = $dateFrom ?? (new DateTime('first day of this month'))->format('Y-m-d');
        $dateTo = $dateTo ?? (new DateTime('last day of this month'))->format('Y-m-d');

        if ($dateFrom > $dateTo) {
            return 'Invalid interval!';
        }

        $from = new DateTime($dateFrom);
        $to = new DateTime($dateTo);

        $interval = $to->diff($from)->format('%a') + 1;

        $previousFrom = date('Y-m-d', strtotime('-' . ($interval) . ' day', strtotime($dateFrom)));
        $previousTo = date('Y-m-d', strtotime('-' . ($interval) . ' day', strtotime($dateTo)));

        $result = array(
            'current' => $this->calculate($dateFrom, $dateTo, $interval),
            'previous' => $this->calculate($previousFrom, $previousTo, $interval)
        );

        return $result;
    }
}
