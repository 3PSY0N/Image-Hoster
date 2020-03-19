<?php

namespace App\Core;

use \PDO;

class Database
{
    /** @var Database $instance */
    private static $instance;
    /** @var PDO $pdo */
    private $pdo;

    /**
     * @return Database
     */
    public static function getPDO(): Database
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    /**
     * @param $config
     */
    public function setup($config): void
    {
        $dsn     = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $config['user'], $config['password'], $options);
        } catch (\PDOException $e) {
            echo "ERROR: " . $e->getMessage();
            die();
        }
    }

    /**
     * @param $sql
     * @param array $arrayArguments
     * @return mixed
     */
    public function fetch($sql, $arrayArguments = [])
    {
        return $this->privateFetch($sql, $arrayArguments, 'fetchObject');
    }

    /**
     * @param $sql
     * @param $arrayArguments
     * @param $fetch
     * @param null $mode
     * @return mixed
     */
    private function privateFetch($sql, $arrayArguments, $fetch, $mode = null)
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($arrayArguments);

        return $stmt->$fetch($mode);
    }

    /**
     * @param $sql
     * @param array $arrayArguments
     * @return mixed
     */
    public function fetchAll($sql, $arrayArguments = [])
    {
        return $this->privateFetch($sql, $arrayArguments, 'fetchAll', PDO::FETCH_OBJ);
    }

    /**
     * IUD for Insert Update Delete
     * @param $sql
     * @param array $arrayArguments
     * @return mixed
     */
    public function IUD($sql, $arrayArguments = [])
    {
        return $this->privateIUD($sql, $arrayArguments);
    }

    /**
     * @param $sql
     * @param array $arrayArguments
     * @return bool
     */
    private function privateIUD($sql, $arrayArguments = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($arrayArguments);
        return $stmt->rowCount();
    }
}
