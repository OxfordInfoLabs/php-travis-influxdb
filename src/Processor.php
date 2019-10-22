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

            $travisCIWorker = new TravisCIWorker($config["endpoint"], $config["accessToken"] ?? null);

            foreach ($config["repositories"] as $repository) {

                $travisStatus = $travisCIWorker->getCurrentBuildInfo($repository);


                if ($this->config["useBuildTimestamps"] ?? false) {

                    $latestForRepo = $influxWorker->getLatestFilteredMetric($configName, "repo", $repository);
                    if ($latestForRepo && $latestForRepo["last_run"] == $travisStatus["finished_at"])
                        continue;

                }

                // Get the short name from the repo by getting the last fragment.
                $explodedRepo = explode("/", $repository);
                $repoShortName = array_pop($explodedRepo);

                $state = $travisStatus["state"] ?? "failed";

                if ($state == "failed") $result = 0;
                else if ($state == "finished")
                    $result = $travisStatus["result"] ?? 1 ? 0 : 1;
                else
                    $result = 2;

                $status = $result == 1 ? "Succeeded" :
                    ($result == 2 ? "Running" : "Failed");


                $influxWorker->writeMetric($configName, $result, ["repo" => $repository, "short_name" => $repoShortName, "status" => $status,
                    "last_run" => $travisStatus["finished_at"]]);

            }


        }

    }

}
