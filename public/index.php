<?php

require_once(dirname(__DIR__) . '/vendor/autoload.php');

\app\Boot::bootstrap(
    \app\App::app('app', require(dirname(__DIR__) . "/config/app-config.ignore.php"))
)->run(\app\Request::createFromGlobals(), new \app\Response());