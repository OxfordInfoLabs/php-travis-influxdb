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
     * @var string
     */
    private $accessToken;


    /**
     * Construct with required data.
     */
    public function __construct($endpoint, $accessToken) {
        $this->endpoint = $endpoint;
        $this->accessToken = $accessToken;
    }


    /**
     * Get current build status for a given repository
     *
     * @param $repository
     */
    public function getCurrentBuildInfo($repository) {

        $header = "User-Agent: MyClient/1.0.0\r\n";

        if ($this->accessToken)
            $header .= "Authorization: token " . $this->accessToken;

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => $header
            ]
        ];

        $context = stream_context_create($opts);

        $data = file_get_contents($this->endpoint . "/repos/$repository/builds",false, $context);

        $data = json_decode($data, true);

        return $data[0] ?? [];

    }

}
