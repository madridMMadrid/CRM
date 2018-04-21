<?php
/**
 * Database.php Database connection with .env file
 *
 * PHP Version 7
 *
 * @category Class
 * @package  Core
 * @author   Vlodimir Pavlov <author@example.com>
 * @author   Daniil Gorelov <dgorelov@p-food.ru>
 * @link     http://www.p-food.ru
 */
namespace Core\Config;

use Dotenv\Dotenv;
use SafeMySQL;

/**
 * Class Database
 *
 * @package Core
 */
class Database
{
    protected $host;
    protected $user;
    protected $password;
    protected $database;
    protected $port;
    protected $db;

    private $_env;
    private $_config;

    /**
     * Database constructor.
     */
    function __construct()
    {
        $this->_env = new Dotenv(__DIR__ . "/../../", '.env');
        $this->_env->load();

        $this->_env->required(['DB_HOST', 'DB_USER', 'DB_PASSWORD', 'DB_DATABASE', 'DB_PORT']);
        $this->setupConfig();
        $this->db = new SafeMySQL($this->getConfig());
    }


    /**
     * @return array
     */
    protected function getConfig():array
    {
        return $this->_config;
    }

    /**
     * @return array
     */
    protected function setupConfig()
    {
        $this->host = getenv('DB_HOST');
        $this->user = getenv('DB_USER');
        $this->password = getenv('DB_PASSWORD');
        $this->database = getenv('DB_DATABASE');
        $this->port = getenv('DB_PORT');

        $this->_config = [
          'host' => $this->host,
          'user' => $this->user,
          'pass' => $this->password,
          'db' => $this->database,
          'port' => $this->port,
          'socket' => null,
          'pconnect' => false,
          'charset' => 'utf8',
          'errmode' => 'exception',
          'exception' => 'Exception',
        ];
    }
}
