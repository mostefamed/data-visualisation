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

class ThermometerChartsController extends AbstractController
{
    private ?\PDO $connection;

    public function __construct(GetDefaultDatabaseConnection $getDefaultDatabaseConnection)
    {
        $this->connection = ($getDefaultDatabaseConnection)();
    }

    #[Route('/charts/thermometer/count-by-day', name : 'app_charts_thermometer_count_by_day')]
    public function countByDay(GetDefaultDatabaseConnection $getDefaultDatabaseConnection, QueryManager $queryManager): Response
    {
        if (!$this->connection) {
            return $this->render('query_handler/index.html.twig', [
                'title' => 'Query',
                'error' => 'Connection failed. Try again.',
            ]);
        }

        $timestamps = [];
        $query = 'SELECT DATE(timestamp) FROM AC_Thermometer_Log GROUP BY DATE(timestamp)';
        $items = $queryManager->process($this->connection, $query, 3);

        foreach ($items as $item) {
            $timestamps[] = current($item);
        }

        return $this->render('charts_thermometer/countByDay.html.twig', [
             'title' => 'Thermometer Charts - Count by day',
             'timestamps' => $timestamps,
         ]);
    }

    /**
     * @throws Exception
     */
    #[Route(
         '/ajax/thermometer/data-visualization/count-by-day',
         name: 'ajax_thermometer_visualization_count_by_day',
         methods: ['POST'],
         condition: 'request.isXmlHttpRequest()'

    )]
    public function processDataVisualizationCountByDay(Request $request, GetDefaultDatabaseConnection $getDefaultDatabaseConnection, QueryManager $queryManager): JsonResponse
    {
        $timestamp = $request->get('timestamp');

        if (!$this->connection) {
            throw new Exception('Failed to connect!');
        }

        $query = "
                SELECT  thermometer_id as str_place,
                        COUNT(*) AS count
                FROM AC_Thermometer_Log
                WHERE DATE(timestamp) ='$timestamp' 
                GROUP BY thermometer_id";

        $items = $queryManager->process($this->connection, $query, \PDO::FETCH_ASSOC);
        $header = array_keys($items[0]);

        $data = [
            'columns' => $header,
            'rows' => $items,
            'title' => 'Temperature measurement count by places',
        ];

        return (new JsonResponse($data))->setEncodingOptions(JSON_NUMERIC_CHECK);
    }


    #[Route('/charts/thermometer/temp-average-by-day', name: 'app_charts_thermometer_temp_average_by_day')]
    public function temperatureAverageByDay(GetDefaultDatabaseConnection $getDefaultDatabaseConnection, QueryManager $queryManager): Response
    {
        if (!$this->connection) {
            return $this->render('query_handler/index.html.twig', [
                'title' => 'Query',
                'error' => 'Connection failed. Try again.',
            ]);
        }

        $thermometerIds = [];

        $query = 'SELECT thermometer_id FROM AC_Thermometer_Log GROUP BY thermometer_id';
        $items = $queryManager->process($this->connection, $query, 3);

        foreach ($items as $item) {
            $thermometerIds[] = current($item);
        }

        return $this->render('charts_thermometer/temperatureAverageByDay.html.twig', [
             'title' => 'Thermometer Charts - Average By day',
             'thermometerIds' => $thermometerIds,
         ]);
    }

    /**
     * @throws Exception
     */
    #[Route(
         '/ajax/thermometer/data-visualization/temp-by-place',
         name: 'ajax_thermometer_visualization_temp_by_place',
         methods: ['POST'],
         condition: 'request.isXmlHttpRequest()'
    )]
    public function processDataVisualizationTemperatureByPlace(Request $request, GetDefaultDatabaseConnection $getDefaultDatabaseConnection, QueryManager $queryManager): JsonResponse
    {
        $thermometerId = $request->get('thermometerId');

        if (!$this->connection) {
            throw new Exception('Failed to connect!');
        }

        $query = "
                SELECT  DATE(timestamp) as str_day,
                        AVG(temperature) AS avg

                FROM AC_Thermometer_Log
                WHERE thermometer_id ='$thermometerId' 
                GROUP BY DATE(timestamp)";

        $items = $queryManager->process($this->connection, $query, \PDO::FETCH_ASSOC);
        $header = array_keys($items[0]);

        $data = [
            'columns' => $header,
            'rows' => $items,
            'title' => 'Place temperature average by day',
        ];

        return (new JsonResponse($data))->setEncodingOptions(JSON_NUMERIC_CHECK);
    }
}
