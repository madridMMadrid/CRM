<?php

namespace Core;

use App\UrlShortener;
use Core\Config\Settings;
use Doctrine\Common\Inflector\Inflector;
use ReflectionMethod;

/**
 * Class APIUnauthorized
 */
class APIUnauthorized
{
    protected $pd;
    protected $settings;
    protected $url;
    protected $user;
    protected $shortUrl;

    /**
     * APIUnauthorized constructor.
     */
    public function __construct()
    {
        $this->pd = new ParseData();
        $this->settings = new Settings();
        $this->url = App::url();
        $this->shortUrl = new UrlShortener();

        // Call the router.
        if (count($this->url) > 1) {
            $this->prepareMethod($this, $this->url[1]);
        }
    }

    /**
     * Prepare and call method.
     *
     * @param $class
     * @param $method
     */
    protected function prepareMethod($class, $method)
    {
        if ($this->isAuthorized($method)) {
            $reflector = new ReflectionMethod($class, $method);
            $declared = $reflector->getParameters();
            $parameters = array();

            // Filling the array.
            for ($i = 0; $i < count($declared); $i++) {
                if (isset($_POST[$declared[$i]->name])) {
                    $parameters[] = $_POST[$declared[$i]->name];
                } else {
                    $parameters[] = null;
                }
            }

            // Calling the method
            echo json_encode($reflector->invokeArgs($this, $parameters));
        } else {
            echo json_encode('error', 'Fuck you, asshole!');
            die();
        }
    }

    /**
     * Check if the user is authorized to execute the procedure.
     *
     * @param $method
     * @return bool
     */
    protected function isAuthorized($method)
    {
        if ($method == 'login' || $method == 'addRequest') {
            return true;
        } else {
            $permissions = $this->pd->getPermissions($method);
            if ($permissions) {
                if (in_array($this->user['employeeType'], $permissions) || $this->user['employeeType'] == 5) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return false;
    }
}
