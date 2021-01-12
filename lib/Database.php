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

    public function query($query)
    {
        $this->__connection->prepare($query)->execute();
        return $this;
    }

    public function prepareQuery($query, $params)
    {
        $this->__connection = $this->__connection->prepare($query);
        $this->__connection->execute($params);
        
        return $this;
    }

    public function first()
    {
        return $this->__connection->fetch();
    }

    public function get()
    {
        return $this->__connection->fetchAll();
    }

    public function count()
    {
        return $this->__connection->rowCount();
    }

    public function exists()
    {
        return ($this->count() > 0);
    }
}
