<?php
require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use app\App;
use app\Boot;
use app\Libs\PushHelper;
use app\Request;
use app\Util;
use Tiny\Interfaces\RequestInterface;

Boot::bootstrap(
    App::app('app', require(dirname(dirname(__DIR__)) . "/config/app-config.ignore.php"))
);

$request = Request::createFromGlobals();
Boot::tryStartSession($request);

header("Content-Type:application/json;charset=utf-8");

$token = $request->_get('token', '');
$develop_key = App::config('ENV_DEVELOP_KEY', '');
if ($develop_key != $token && $develop_key != App::decrypt($token)) {
    $result = ['code' => 500, 'msg' => 'error token'];
} else {
    $rst = [];
    $action = Util::trimlower($request->_get('action', ''));

    if ($action == 'save_num') {
        $rst = _save_num($request);
    } else if ($action == 'save_record') {
        $rst = _save_record($request);
    } else if ($action == 'dms_msg') {
        $rst = _save_dms_msg($request);
    } else {
        $rst = ['code' => 500, 'msg' => 'error action'];
    }
    $result = array_merge(['code' => 0, 'action' => $action], $rst);
}
# 不使用 try catch 提高性能

exit(json_encode($result));

function _save_dms_msg(RequestInterface $request)
{
    $clientId = $request->_get('clientId', '');
    $msgId = $request->_get('msgId', 0);
    $peerId = $request->_get('peerId', 0);
    $qos = $request->_get('qos', 0);
    $retain = $request->_get('retain', 0);
    $time_stamp = $request->_get('time_stamp', 0);
    list($msgId, $peerId, $qos, $retain, $time_stamp) = [intval($msgId), intval($peerId), intval($qos), intval($retain), intval($time_stamp)];

    $topic = $request->_get('t', '');
    $m = $request->_get('m', '');
    $msg = !empty($m) ? json_decode($m, true) : [];

    return PushHelper::actionDmsTopicMsg($topic, $msg, $clientId, $msgId, $peerId, $qos, $retain, $time_stamp);
}

function _save_num(RequestInterface $request)
{
    # 'num=%s&timer_count=%s&timer_interval=%s&time_now=%s' % (num, timer_count, timer_interval, time_now)
    $room_id = $request->_get('room_id', 0);
    $num = $request->_get('num', 0);
    $timer_count = $request->_get('timer_count', 0);
    $timer_interval = $request->_get('timer_interval', 0);
    $time_now = $request->_get('time_now', 0);
    list($room_id, $num, $timer_count, $timer_interval, $time_now) = [intval($room_id), intval($num), intval($timer_count), intval($timer_interval), intval($time_now)];

    return PushHelper::saveRoomRunning($room_id, $num, $timer_count, $timer_interval, $time_now);
}

function _save_record(RequestInterface $request)
{
    # 's_id=%s&time_now=%s' % (s_id, time_now)
    $room_id = $request->_get('room_id', 0);
    $s_id = $request->_get('s_id', '');
    $time_now = $request->_get('time_now', 0);
    list($room_id, $s_id, $time_now) = [intval($room_id), strval($s_id), intval($time_now)];

    return PushHelper::saveRoomRecord($room_id, $s_id, $time_now);
}