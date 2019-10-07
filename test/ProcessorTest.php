<?php

namespace Oxil\PHPTravisInflux;

use InfluxDB\Client;
use PHPUnit\Framework\TestCase;

include_once __DIR__ . "/autoloader.php";

/**
 * Test processor gets
 */
class ProcessorTest extends TestCase {


    /**
     * @var Database
     */
    private $database;

    public function setUp(): void {

        $influxDb = new Client("localhost");
        $this->database = $influxDb->selectDB("example");
        $this->database->query("DROP measurement travis_ci_org");
    }

    public function testCanProcessBasedOnSuppliedConfig() {

        $processor = new Processor();

        // Process all metrics using configuration
        $processor->process();

        $results = $this->database->query("select * from travis_ci_org LIMIT 10");
        $points = $results->getPoints();

        $this->assertEquals(3, sizeof($points));

        var_dump($points);
    }
}