<?php

require(dirname(__DIR__) . "/config/app-config.ignore.php");

\app\Bootstrap::bootstrap(
    'app',
    new \Tiny\Application(require(dirname(__DIR__) . "/config/app-config.ignore.php"))
)->run();