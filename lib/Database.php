<?php

namespace Lib;

class Database
{
    protected $__connection;
    protected $__configs;

    public function __construct()
    {
        try {
            $this->__configs = require 'config.php';

            $sys_config_database = $this->__configs['database'];
            $dsn = $sys_config_database['engine'] . ':' . 'dbname=' . $sys_config_database['database_name'] . ';' . 'host=' . $sys_config_database['host'];
            $this->__connection = new \PDO($dsn, $sys_config_database['user'], $sys_config_database['password']);
        } catch (\PDOException $e) {
            throw $e;
        }
    }
}
