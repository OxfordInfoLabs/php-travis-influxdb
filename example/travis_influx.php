<?php

include_once "../vendor/autoload.php";

$processor = new \Oxil\PHPTravisInflux\Processor(__DIR__ . "/config.json");
$processor->process();
