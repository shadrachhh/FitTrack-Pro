<?php

namespace App\Framework;

use PDO;

abstract class Repository
{
    protected PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }
}