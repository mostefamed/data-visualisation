<?php

declare(strict_types=1);

namespace App\Services;

class QueryManager
{
    public function sanitize(string $query): string
    {
        return trim(str_replace("\n","",$query));
    }

    /**
     * @return array<int, array{key: string, value: string}> $items
     */
    public function process(\PDO $connection, string $query, int $fetchOption): array
    {
        $items = [];

        try {
            $stmt = $connection->prepare($query);
            $stmt->execute();

            $stmt->setFetchMode($fetchOption);
            foreach ($stmt->fetchAll() as $record) {
                $items[] = $record ?: [];
            }
        } catch (\PDOException $e) {
            echo 'Error: '.$e->getMessage();
        }

        return $items;
    }
}
