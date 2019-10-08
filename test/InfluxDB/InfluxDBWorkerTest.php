<?php

namespace Oxil\PHPTravisInflux\InfluxDB;

use InfluxDB\Client;
use InfluxDB\Database;
use PHPUnit\Framework\TestCase;

include_once __DIR__ . "/../autoloader.php";

/**
 * Created by PhpStorm.
 * User: mark
 * Date: 07/10/2019
 * Time: 16:11
 */
class InfluxDBWorkerTest extends TestCase {

    /**
     * @var Database
     */
    private $database;

    public function setUp(): void {

        $influxDb = new Client("localhost");
        $this->database = $influxDb->selectDB("example");
        $this->database->create(null);
        $this->database->query("DROP measurement test");
    }

    public function testCanWriteAndReadMetrics() {

        $influxDBWorker = new InfluxDBWorker("localhost", 8086, null, null, "example");
        $influxDBWorker->writeMetric("test", "100", ["late" => 1]);

        $results = $this->database->query("SELECT * FROM test");
        $points = $results->getPoints();

        $this->assertEquals(1, sizeof($points));
        $this->assertEquals(1, $points[0]["late"]);
        $this->assertEquals(100, $points[0]["value"]);

        $reMetric = $influxDBWorker->readMetric("test", 3);
        $this->assertEquals($points, $reMetric);


    }

}
