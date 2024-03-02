<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contracts\PerformDatabaseConnectionInterface;
use App\Services\QueryManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class QueryHandlerController extends AbstractController
{
    #[Route('/query', name: 'app_query_handler')]
    public function index(Request $request, QueryManager $queryManager, PerformDatabaseConnectionInterface $performDatabaseConnection): Response
    {
        if ('POST' === $request->getMethod()) {
            [
                'databaseName' => $databaseName,
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'password' => $password,

            ] = $request->get('data');

            $connection = ($performDatabaseConnection)($host, $databaseName, $username, $password, (int) $port);

            if (!$connection) {
                return $this->render('query_handler/index.html.twig', [
                    'title' => 'Query',
                    'error' => 'Connection failed. Try again.',
                ]);
            }

            $query = $queryManager->sanitize($request->get('query'));

            $items = $queryManager->process($connection, $query, \PDO::FETCH_ASSOC);

            return $this->render('query_handler/result.html.twig', [
              'title' => 'Query result',
              'items' => $items,
              'header' => array_keys($items[0]),
              'query' => $query,
            ]);
        }

        return $this->render('query_handler/index.html.twig', [
            'title' => 'Query',
        ]);
    }
}
