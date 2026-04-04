<?php

require_once __DIR__ . '/../src/Framework/Database.php';

use App\Framework\Database;

try {
    $db = Database::getConnection();
    echo "DB CONNECTED ✅";
} catch (Exception $e) {
    echo "DB FAILED ❌";
}