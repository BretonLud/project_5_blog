<?php

namespace App\Database;

use PDO;

class Connection
{
    public ?PDO $database = null;
    
    /**
     * @return PDO
     */
    public function getConnection(): PDO
    {
        if ($this->database === null) {
            $this->database = new PDO(
                'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=utf8',
                $_ENV['DB_USER'], $_ENV['DB_PASS']
            );
        }
        
        return $this->database;
    }
}