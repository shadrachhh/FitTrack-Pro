<?php

namespace App\Framework;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    public static function getConnection(): PDO
    {
        $host = 'db';         
        $db   = 'fittrack';
        $user = 'root';
        $pass = 'root';

        try {
            return new PDO(
                "mysql:host=$host;dbname=$db;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]
            );
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed.', 0, $e);
        }
    }
}
