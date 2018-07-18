<?php

namespace Classes\Model\Connection;

use Classes\Env\DotEnv;

/**
 * Class Connection
 *
 * Database connector class
 *
 * @package Classes\Model\Connection
 */
class Connection
{
    private $_connection;
    private static $_instance;
    private $_host;
    private $_username;
    private $_password;
    private $_database;

    /**
     * Singleton element for connection creation
     *
     * @return Db
     */
    public static function getInstance()
    {
        if (!self::$_instance) { // If no instance then make one
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Db constructor.
     */
    private function __construct()
    {
        try {

            $path  = ( php_sapi_name() == 'cli' ) ? __DIR__ . '/../../../.env.php' : '../.env.php';

            DotEnv::load($path);
            $dbConfig = DotEnv::get('DATABASE', null);

            $this->_host = $dbConfig['DB_HOST'];
            $this->_database = $dbConfig['DB_NAME'];
            $this->_username = $dbConfig['DB_USER'];
            $this->_password = $dbConfig['DB_PASSWORD'];

            $this->_connection = new \PDO("mysql:host=$this->_host;dbname=$this->_database", $this->_username, $this->_password);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Singleton element for connection creation
     */
    private function __clone()
    {
    }

    /**
     * DB connection getter
     *
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Returns the last inserted element ID
     *
     * @return string
     */
    public function lastInsertId()
    {
        return $this->_connection->lastInsertId();
    }

}