<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PerformDatabaseConnectionInterface;

class PerformDatabaseConnection implements PerformDatabaseConnectionInterface
{
    public function __invoke(string $host, string $databaseName, string $username, string $password, int $port): ?\PDO
    {
        try {
            $connection = new \PDO("mysql:host=$host;dbname=$databaseName; port=$port", $username, $password);
            $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo 'Connection failed: '.$e->getMessage();
            $connection = null;
        }

        return $connection;
    }
}
