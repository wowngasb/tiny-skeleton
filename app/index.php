<?php

require_once(dirname(__DIR__) . '/vendor/autoload.php');

\app\Bootstrap::bootstrap(
    \Tiny\Application::app('app', require( dirname(__DIR__) . "/config/app-config.ignore.php" ))
)->run(new \Tiny\Request(), new \Tiny\Response());