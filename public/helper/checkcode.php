<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use app\App;
use app\Boot;
use app\Request;

Boot::bootstrap(
    App::app('app', require(dirname(dirname(__DIR__)) . "/config/app-config.ignore.php"))
);

$request = Request::createFromGlobals();
Boot::tryStartSession($request);

header("Content-Type:image/jpeg");

require_once(PLUGIN_PATH . 'captcha/captcha.php');

$captcha = new captcha();
$captcha->setImSize(96, 34);
$captcha->create();
$captcha->drawCurve();
$_checkcode = strtolower($captcha->getCodeChar(true));
\app\api\AuthMgr::_setCheckCode($request, $_checkcode);
$captcha->display();