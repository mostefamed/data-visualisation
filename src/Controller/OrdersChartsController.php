<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\GetDefaultDatabaseConnection;
use App\Services\QueryManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrdersChartsController extends AbstractController
{
    private ?\PDO $connection;

    public function __construct(GetDefaultDatabaseConnection $getDefaultDatabaseConnection)
    {
        $this->connection = ($getDefaultDatabaseConnection)();
    }

    /**
     * @throws Exception
     */
    #[Route('/charts/orders', name: 'app_charts_orders')]
    public function index(QueryManager $queryManager): Response
    {
        $orderDates = [];

        if (!$this->connection) {
            throw new Exception('Failed to connect!');
        }

        $query = 'SELECT order_date FROM orders GROUP BY order_date';
        $items = $queryManager->process($this->connection, $query, 3);

        foreach ($items as $item) {
            $orderDates[] = current($item);
        }

        return $this->render('charts_orders/index.html.twig', [
             'title' => 'Orders Charts',
             'orderDates' => $orderDates,
         ]);
    }

    /**
     * @throws Exception
     */
    #[Route(
         '/ajax/orders/data-visualization',
         name: 'ajax_orders_visualization',
         methods: ['POST'],
         condition: 'request.isXmlHttpRequest()'
    )]
    public function processDataVisualization(
        Request $request,
        GetDefaultDatabaseConnection $getDefaultDatabaseConnection,
        QueryManager $queryManager
    ): JsonResponse
    {
        $orderDate = $request->get('orderDate');

        if (!$this->connection) {
            throw new Exception('Failed to connect!');
        }

        $query = "
                SELECT  name as str_customer_name,
                        COUNT(*) AS count
                FROM orders 
                INNER JOIN customers ON customers.id = orders.customer_id
                WHERE order_date ='$orderDate' GROUP BY customer_id";

        $items = $queryManager->process($this->connection, $query, \PDO::FETCH_ASSOC);
        $header = array_keys($items[0]);

        $data = [
            'columns' => $header,
            'rows' => $items,
            'title' => 'Orders count by customers',
        ];

        // JSON_NUMERIC_CHECK: to preserve number type
        return (new JsonResponse($data))->setEncodingOptions(JSON_NUMERIC_CHECK);
    }
}
