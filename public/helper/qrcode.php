<?php

use app\App;
use app\Boot;
use app\Request;

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

Boot::bootstrap(
    App::app('app', require(dirname(dirname(__DIR__)) . "/config/app-config.ignore.php"))
);

$request = Request::createFromGlobals();
Boot::tryStartSession($request);

require PLUGIN_PATH . 'QRcode/phpqrcode.php';

$text = $request->_get('text', $request->host());
$errorCorrectionLevel = $request->_get('level', 'L');
$matrixPointSize = $request->_get('size', 6);
$padding = $request->_get('padding', 1);

QRcode::png($text, false, $errorCorrectionLevel, $matrixPointSize, $padding);