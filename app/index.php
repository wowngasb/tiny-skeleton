<?php

require_once(dirname(__DIR__) . '/vendor/autoload.php');

\app\Bootstrap::bootstrap(
    'app',
    new \Tiny\Application(require(dirname(__DIR__) . "/config/app-config.ignore.php"))
)->run( new \Tiny\Request($_ENV, $_SERVER, $_GET, $_POST, $_REQUEST, $_COOKIE, $_SESSION, $_FILES), new \Tiny\Response());