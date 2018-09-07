<?php

use app\App;
use app\Boot;
use app\Request;

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

Boot::bootstrap(
    App::app('app', require(dirname(dirname(__DIR__)) . "/config/app-config.ignore.php"))
);

$request = Request::createFromGlobals();

$clientId = $request->_request('clientId');
$clientId = !empty($clientId) ? trim($clientId) : uniqid();
$pubKey = $request->_request('pubKey');
$pubKey = !empty($pubKey) ? trim($pubKey) : uniqid();

$tSeq = intval(time() / 10);
$dms_key = App::config('services.black.key');
$subKey = md5("{$dms_key}_{$clientId}_{$pubKey}_{$tSeq}");

$mqtt = [
    'code' => 0,
    'subKey' => $subKey,
    'pubKey' => $pubKey,
    'clientId' => $clientId,
    'dms_host' => App::config('services.black.dms_host'),
];

$json = json_encode($mqtt);
$callback = $request->_request('callback');

if (!empty($callback)) {
    header("Content-Type:application/javascript;charset=utf-8");
    header('Pragma:no-cache', true);
    header("Cache-Control:max-age=60", true);
    $ret = "{$callback}($json);";
} else {
    header("Content-Type:application/json;charset=utf-8");
    $ret = $json;
}
exit($ret);