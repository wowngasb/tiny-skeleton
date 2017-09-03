<?php
use app\Bootstrap;

require(dirname(__DIR__) . "/config/app-config.ignore.php");

Bootstrap::bootstrap(
    'app',
    new \Tiny\Application(require(dirname(__DIR__) . "/config/app-config.ignore.php"))
)->run();