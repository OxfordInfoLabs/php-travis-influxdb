<?php

namespace Oxil\PHPTravisInflux\TravisCI;

use PHPUnit\Framework\TestCase;

include_once __DIR__ . "/../autoloader.php";

/**
 * Test cases for Travis CI worker.
 *
 */
class TravisCIWorkerTest extends TestCase {

    /**
     * @var TravisCIWorker
     */
    private $worker;

    /**
     * Set up
     */
    public function setUp(): void {
        $config = json_decode(file_get_contents(__DIR__ . "/../Config/config.json"), true);
        $this->worker = new TravisCIWorker($config["travis"]["travis_ci_org"]["endpoint"], null);
    }

    public function testCanGetCurrentBuildInfoForRepo() {

        $buildInfo = $this->worker->getCurrentBuildInfo("OxfordInfoLabs/kinikit-core");

        $this->assertNotNull($buildInfo["state"]);
        $this->assertNotNull($buildInfo["finished_at"]);

    }


}
