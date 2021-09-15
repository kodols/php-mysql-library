<?php

namespace Kodols\MySQL;

use \Kodols\MySQL\Native;
use \Kodols\MySQL\Helpers;
use \Kodols\MySQL\Configuration;
use \Kodols\MySQL\Builder\Insert;
use \Kodols\MySQL\Builder\Replace;
use \Kodols\MySQL\Builder\Ignore;
use \Kodols\MySQL\Builder\Select;
use \Kodols\MySQL\Builder\Delete;
use \Kodols\MySQL\Builder\Update;
use \Kodols\MySQL\Report;
use \Exception;
use \PDO;

class Server
{
    use Native;
    use Helpers;
    use Report;

    const CONNECTED = 1;
    const DISCONNECTED = 2;

    private $pdo;
    private $pdo_connection_time = 0;
    private $state;
    private $configuration;
    private $builders = [];
    private static $queryLog = [];

    public function __construct(Configuration $configuration)
    {
        $this->state = self::DISCONNECTED;
        $this->configuration = $configuration;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    protected function createPDO()
    {
        // do not connect if already connected
        if ($this->state == self::CONNECTED) {
            // do not connect if the grace timeout not reached
            if ((time() - $this->pdo_connection_time) <= $this->configuration->getTimeout()) {
                return $this->pdo;
            }

            // require a fresh connection
            $this->pdo = null;
            $this->state = self::DISCONNECTED;
        }

        $dsn = 'mysql:host=' . $this->configuration->getHostname() . ';';
        $dsn .= 'port=' . $this->configuration->getPort() . ';';
        $dsn .= 'dbname=' . $this->configuration->getDatabase();

        $options = [];
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        $options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_OBJ;
        $options[PDO::ATTR_PERSISTENT] = $this->configuration->getPersistent() ? true : false;

        $command = '';
        if ($encoding = $this->configuration->getEncoding()) {
            $command .= 'SET NAMES \'' . $encoding . '\'';
        }

        if (!empty($command)) {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = $command;
        }

        $this->pdo = new PDO($dsn, $this->configuration->getUsername(), $this->configuration->getPassword(), $options);
        $this->pdo_connection_time = time();
        $this->state = self::CONNECTED;
        return $this->pdo;
    }

    public function getNativePdo()
    {
        return $this->createPDO();
    }

    public function build($format, array $options = [])
    {
        if ($format == 'insert') {
            return new Insert($this, $options);
        } elseif ($format == 'replace') {
            return new Replace($this);
        } elseif ($format == 'ignore') {
            return new Ignore($this);
        } elseif ($format == 'select') {
            return new Select($this);
        } elseif ($format == 'delete') {
            return new Delete($this);
        } elseif ($format == 'update') {
            return new Update($this);
        } else {
            throw new Exception('Requested build format "' . $format . '" does not exist."');
        }
    }

    public function isLogEnabled()
    {
        return (bool)$this->configuration->getLog();
    }

    public function logQuery($format, $query)
    {
        $backtrace = debug_backtrace();
        array_shift($backtrace); // remove builder call

        self::$queryLog[] = [
            'format' => $format,
            'query' => $query,
            'caller' => [
                'filename' => $backtrace[0]['file'],
                'line_number' => $backtrace[0]['line']
            ]
        ];
    }

    public function getQueryLog()
    {
        return self::$queryLog;
    }

}