<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contracts\PerformDatabaseConnectionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DatabaseConnectionController extends AbstractController
{
    #[Route('/database', name: 'app_database_connection')]
    public function index(Request $request, PerformDatabaseConnectionInterface $performDatabaseConnection): Response
    {
        if ('POST' === $request->getMethod()) {
            [
                'host' => $host,
                'databaseName' => $databaseName,
                'username' => $username,
                'password' => $password,
                'port' => $port,
            ] = $request->get('data');

            $connection = ($performDatabaseConnection)($host, $databaseName, $username, $password, (int) $port);

            return $this->render('database_connection/index.html.twig', [
                'title' => 'Connection',
                'isConnected' => (bool)$connection,
                'databaseName' => $databaseName,
            ]);
        }

        return $this->render('database_connection/index.html.twig', [
            'title' => 'Connection',
        ]);
    }
}
