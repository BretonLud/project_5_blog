<?php

namespace App\Database;

use PDO;

class Connection
{
    public ?PDO $database = null;
    
    public function getConnection(): PDO
    {
        if ($this->database === null) {
            $this->database = new PDO('mysql:host=localhost;dbname=project5;charset=utf8', 'root', 'root');
        }
        
        return $this->database;
    }
}