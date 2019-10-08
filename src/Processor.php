<?php

namespace Oxil\PHPTravisInflux;

use Oxil\PHPTravisInflux\InfluxDB\InfluxDBWorker;
use Oxil\PHPTravisInflux\TravisCI\TravisCIWorker;

/**
 * Main processor which reads from Travis CI and writes to InfluxDB using configured params
 */
class Processor {

    private $config = null;

    /**
     * Processor constructor.
     */
    public function __construct($pathToConfigFile = "Config/config.json") {
        $this->config = json_decode(file_get_contents($pathToConfigFile), true);
    }

    /**
     * Process
     */
    public function process() {

        $influxWorker = new InfluxDBWorker($this->config["influxdb"]["host"] ?? null,
            $this->config["influxdb"]["port"] ?? null,
            $this->config["influxdb"]["username"] ?? null,
            $this->config["influxdb"]["password"] ?? null,
            $this->config["influxdb"]["database"] ?? null);


        foreach ($this->config["travis"] as $configName => $config) {

            $travisCIWorker = new TravisCIWorker($config["endpoint"]);

            foreach ($config["repositories"] as $repository) {

                $travisStatus = $travisCIWorker->getCurrentBuildInfo($repository);

                if ($this->config["useBuildTimestamps"] ?? false) {

                    $latestForRepo = $influxWorker->getLatestFilteredMetric($configName, "repo", $repository);
                    if ($latestForRepo && $latestForRepo["last_run"] == $travisStatus["finished_at"])
                        continue;

                }


                $influxWorker->writeMetric($configName, 1, ["repo" => $repository, "status" => $travisStatus["state"],
                    "last_run" => $travisStatus["finished_at"]]);

            }


        }

    }

}
