<?php


namespace Oxil\PHPTravisInflux\InfluxDB;

use InfluxDB\Client;
use InfluxDB\Database;
use InfluxDB\Point;

/**
 * Influx DB worker.
 */
class InfluxDBWorker {

    private $influxDatabase;

    /**
     * Create an influx DB worker.
     *
     * InfluxDBWorker constructor.
     */
    public function __construct($host = null, $port = null, $username = null, $password = null, $database = null) {
        $influxClient = new Client($host, $port, $username, $password);
        $this->influxDatabase = $influxClient->selectDB($database);

        // Create the database if it doesn't yet exist.
        if (!$this->influxDatabase->exists()) {
            $this->influxDatabase->create();
        }
    }


    /**
     * Write a metric to influx db
     *
     * @param $string
     * @param $int
     * @param $array
     * @param $array1
     * @param $date
     */
    public function writeMetric($measurement, $value, $tags = array(), $additionalFields = array(), $time = null) {

        $point = new Point($measurement, $value, $tags, $additionalFields, $time);
        $this->influxDatabase->writePoints(array($point), Database::PRECISION_SECONDS);
    }


    /**
     * Read metrics up to a limit
     *
     * @param $maxPoints
     */
    public function readMetric($metric, $maxPoints) {
        $results = $this->influxDatabase->query("SELECT * from $metric LIMIT $maxPoints");
        return $results->getPoints();
    }


    /**
     * Get the latest filtered metric
     *
     * @param $metric
     * @param $filters
     */
    public function getLatestFilteredMetric($metric, $filterColumn, $filterValue) {
        $results = $this->influxDatabase->query("SELECT * FROM $metric WHERE $filterColumn = '$filterValue' ORDER BY time DESC LIMIT 1");
        return $results->getPoints()[0] ?? null;
    }


}
