<?php

declare(strict_types=1);

namespace App\Contracts;

interface PerformDatabaseConnectionInterface
{
    public function __invoke(string $host, string $databaseName, string $username, string $password, int $port): ?\PDO;
}
