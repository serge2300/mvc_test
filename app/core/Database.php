<?php

namespace serge2300\MVCTest\Core;

class Database
{
    private static $connection = null;

    private function __construct() 
    {
        global $config;
        $dsn = "{$config['database']['driver']}:dbname={$config['database']['dbname']};host={$config['database']['host']};port={$config['database']['port']}";
        self::$connection = new \PDO($dsn, $config['database']['username'], $config['database']['password']);
    }

    /**
     * Get an instance of database connection
     * 
     * @return null|PDO
     */
    public static function getConnection()
    {
        if (self::$connection == null) {
            self::$connection == new Database();
        }
        return self::$connection;
    }
}