<?php

include_once __DIR__ . "/../vendor/autoload.php";

$processor = new \Oxil\PHPTravisInflux\Processor(__DIR__ . "/config.json");
$processor->process();
