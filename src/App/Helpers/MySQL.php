<?php

namespace Gogol\VpsManager\App\Helpers;

use Gogol\VpsManager\App\Application;

class MySQL extends Application
{
    protected $mysqli;

    public function connect()
    {
        if ( $this->mysqli )
            return $this->mysqli;

        return $this->mysqli = new \mysqli("localhost", "root", null);
    }

    public function dbName($domain)
    {
        return preg_replace("/[^a-z0-9]+/i", '_', $domain);;
    }

    public function createDatabase($domain)
    {
        $database = $this->dbName($domain);
        $password = getRandomPassword();

        $this->connect()->query('CREATE DATABASE IF NOT EXISTS `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
        $this->connect()->query('GRANT ALL PRIVILEGES ON `'.$database.'`.* to `'.$database.'`@`localhost` identified by \''.$password.'\'');
        $this->connect()->query('flush privileges');

        return $this->response()
                    ->success("<info>MySQL databáza úspešne vytvorená</info>\nDatabáza\Používateľ: <comment>$database</comment>\nHeslo: <comment>$password</comment>");
    }
}

?>