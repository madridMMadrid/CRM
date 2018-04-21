<?php

namespace Core\Config;

/**
 * Class Settings
 *
 * @package core
 */
class Settings
{
    public $root;
    public $httpRoot;

    public $jwtKey = 'tsss,itisasecretsuperspysecret';

    public function __construct()
    {
        $this->root = $_SERVER['DOCUMENT_ROOT'];
        $this->httpRoot = 'http://' . $_SERVER['HTTP_HOST'] . '/system/';
    }
}
