<?php

namespace Oxil\PHPTravisInflux\TravisCI;

/**
 * Travis CI reader
 */
class TravisCIWorker {

    /**
     * @var string
     */
    private $endpoint;


    /**
     * Construct with required data.
     */
    public function __construct($endpoint) {
        $this->endpoint = $endpoint;
    }


    /**
     * Get current build status for a given repository
     *
     * @param $repository
     */
    public function getCurrentBuildInfo($repository) {

        $data = file_get_contents($this->endpoint . "/repos/$repository/builds");
        $data = json_decode($data,true);

        return $data[0] ?? [];

    }

}