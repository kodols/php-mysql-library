<?php

namespace Kodols\MySQL;

use \Exception;

class Configuration
{

    // generic pdo connection details
    private $port = 3306;
    private $hostname = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = '';

    // after how many seconds should this library restore a fresh connection?
    private $timeout = 30;

    // should the connection be persistent?
    private $persistent = false;

    // what encoding should be used in this connection?
    private $encoding = 'utf8';

    // should the queries be logged in an array for later access?
    private $log = true;

    public function __call($method, $arguments = [])
    {
        if (substr($method, 0, 3) == 'get') {
            $prop = strtolower(substr($method, 3));
            if (!property_exists($this, $prop)) {
                throw new Exception('Trying to call an unknown configuration variable: ' . $prop);
            }
            return $this->$prop;
        }

        if (substr($method, 0, 3) == 'set') {
            $prop = strtolower(substr($method, 3));
            if (!property_exists($this, $prop)) {
                throw new Exception('Trying to set an unknown configuration variable: ' . $prop);
            }
            if (!isset($arguments[0])) {
                throw new Exception('Trying to set configuration variable ' . $prop . ' without providing its value.');
            }
            $this->$prop = $arguments[0];
            return $this;
        }

        throw new Exception('Trying to call an unknown configuration method: ' . $method);
    }

    public function attach()
    {
        $class = new self(get_object_vars($this));
        return $class;
    }

    public function __construct($properties = [])
    {
        if (!count($properties)) {
            return;
        }

        foreach ($properties as $property => $value) {
            $this->$property = $value;
        }
    }

}