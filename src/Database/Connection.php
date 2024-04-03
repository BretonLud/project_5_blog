<?php

namespace App\Database;

use PDO;

class Connection
{
    
    /**
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return new PDO(
            'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=utf8',
            $_ENV['DB_USER'], $_ENV['DB_PASS']
        );
    }
}