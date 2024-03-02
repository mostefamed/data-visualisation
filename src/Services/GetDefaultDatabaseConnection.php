<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PerformDatabaseConnectionInterface;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GetDefaultDatabaseConnection
{
    private PerformDatabaseConnectionInterface $performDatabaseConnection;

    private ParameterBagInterface $params;

    public function __construct(PerformDatabaseConnectionInterface $performDatabaseConnection, ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->performDatabaseConnection = $performDatabaseConnection;
    }

    /**
     * @throws Exception
     */
    public function __invoke(): ?\PDO
    {
        if (!isset($this->params->get('app')['database'])) {
            throw new Exception('Missing database parameter!');
        }

        [
            'host' => $host, 'port' => $port, 'name' => $name, 'user' => $user, 'password' => $password
        ] = $this->params->get('app')['database'];

        return ($this->performDatabaseConnection)($host, $name, $user, $password, (int) $port);
    }
}
