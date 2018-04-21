<?php

namespace App;

use \Core\API;

/**
 * Class Package
 *
 * @package App
 */
class Package extends API
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Display all the packages.
     */
    public function packagesList()
    {
        return $this->pd->packagesList();
    }

    /**
     * Display all the packages matching the query.
     *
     * @param  $string
     * @return array
     */
    public function packagesSearch($string)
    {
        return $this->pd->packagesSearch($string);
    }
}
