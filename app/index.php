<?php

require_once(dirname(__DIR__) . '/vendor/autoload.php');

\app\Bootstrap::bootstrap(
    \app\App::app('app', require( dirname(__DIR__) . "/config/app-config.ignore.php" ))
)->run(\Tiny\StdRequest::createFromGlobals(), new \Tiny\StdResponse());