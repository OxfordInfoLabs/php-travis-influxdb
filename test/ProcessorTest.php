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
        try {
            $this->database->query("DROP measurement travis_ci_org");
        } catch (\Exception $e) {
            // No worries.
        }
    }

    public function testCanProcessBasedOnSuppliedConfig() {

        $processor = new Processor(__DIR__ . "/Config/config.json");

        // Process all metrics using configuration
        $processor->process();

        $results = $this->database->query("select * from travis_ci_org LIMIT 10");
        $points = $results->getPoints();

        $this->assertEquals(3, sizeof($points));

        $this->assertEquals("OxfordInfoLabs/kinikit-core", $points[0]["repo"]);
        $this->assertNotNull($points[0]["status"]);

        $this->assertEquals("OxfordInfoLabs/kinikit-mvc", $points[1]["repo"]);
        $this->assertNotNull($points[1]["status"]);

        $this->assertEquals("OxfordInfoLabs/kinikit-persistence", $points[2]["repo"]);
        $this->assertNotNull($points[2]["status"]);

    }


    public function testIfUseBuildTimestampsSetToFalseEntriesAreWrittenOnEachRun() {

        $processor = new Processor(__DIR__ . "/Config/config.json");

        // Process once
        $processor->process();

        $results = $this->database->query("select * from travis_ci_org LIMIT 10");
        $points = $results->getPoints();
        $this->assertEquals(3, sizeof($points));
        $this->assertEquals("OxfordInfoLabs/kinikit-core", $points[0]["repo"]);
        $this->assertEquals("OxfordInfoLabs/kinikit-mvc", $points[1]["repo"]);
        $this->assertEquals("OxfordInfoLabs/kinikit-persistence", $points[2]["repo"]);

        // Process again
        $processor->process();


        $results = $this->database->query("select * from travis_ci_org LIMIT 10");
        $points = $results->getPoints();

        $this->assertEquals(6, sizeof($points));
        $this->assertEquals("OxfordInfoLabs/kinikit-core", $points[0]["repo"]);
        $this->assertEquals("OxfordInfoLabs/kinikit-mvc", $points[1]["repo"]);
        $this->assertEquals("OxfordInfoLabs/kinikit-persistence", $points[2]["repo"]);
        $this->assertEquals("OxfordInfoLabs/kinikit-core", $points[3]["repo"]);
        $this->assertEquals("OxfordInfoLabs/kinikit-mvc", $points[4]["repo"]);
        $this->assertEquals("OxfordInfoLabs/kinikit-persistence", $points[5]["repo"]);


    }


    public function testIfUseBuildTimestampsSetToTrueEntriesAreOnlyWrittenForNewEntries() {


        $processor = new Processor(__DIR__ . "/Config/config.alt.json");

        // Process once
        $processor->process();

        $results = $this->database->query("select * from travis_ci_org LIMIT 10");
        $points = $results->getPoints();
        $this->assertEquals(3, sizeof($points));
        $this->assertEquals("OxfordInfoLabs/kinikit-core", $points[0]["repo"]);
        $this->assertEquals("OxfordInfoLabs/kinikit-mvc", $points[1]["repo"]);
        $this->assertEquals("OxfordInfoLabs/kinikit-persistence", $points[2]["repo"]);

        // Process again
        $processor->process();


        $results = $this->database->query("select * from travis_ci_org LIMIT 10");
        $points = $results->getPoints();
        $this->assertEquals(3, sizeof($points));
        $this->assertEquals("OxfordInfoLabs/kinikit-core", $points[0]["repo"]);
        $this->assertEquals("OxfordInfoLabs/kinikit-mvc", $points[1]["repo"]);
        $this->assertEquals("OxfordInfoLabs/kinikit-persistence", $points[2]["repo"]);

    }

}
