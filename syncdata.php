<?php

use app\Model\RoomRunning;
use app\Model\RoomRunningDms;
use app\Model\RoomRunningDmsRef;
use app\Model\RoomRunningDmsSum;
use app\Model\RoomRunningSum;
use app\Model\RoomViewRecord;
use app\Model\RoomViewRecordDms;

require_once(__DIR__ . '/vendor/autoload.php');
$app = \app\Boot::bootstrap(
    \app\App::app('app', require(__DIR__ . "/config/app-config.ignore.php"))
);

$token = 'jbiM9SA12C34DS634iX4PM3g';
$host = 'www.xdysoft.com';
$step = 5000;
$maxlen = 100 * 10000;
$buffer_len = 100;

_syncRoomRunning($host, $token, $step, $maxlen, $buffer_len, 'room_running');
_syncRoomRunning($host, $token, $step, $maxlen, $buffer_len, 'room_running_sum');
_syncRoomRunning($host, $token, $step, $maxlen, $buffer_len, 'room_running_dms');
_syncRoomRunning($host, $token, $step, $maxlen, $buffer_len, 'room_running_dms_ref');
_syncRoomRunning($host, $token, $step, $maxlen, $buffer_len, 'room_running_dms_sum');

_syncRoomViewRecord($host, $token, $step, $maxlen, $buffer_len, 0);
_syncRoomViewRecord($host, $token, $step, $maxlen, $buffer_len, 1);

function _syncRoomRunning($host, $token, $step, $maxlen, $buffer_len, $target = 'room_running')
{
    $base_url = "http://{$host}/api/HotFix/syncTableRoomRunning";
    if ($target == 'room_running_dms') {
        $start_id = RoomRunningDms::firstItem([], ['id', 'desc'])->id;
    } elseif ($target == 'room_running_dms_ref') {
        $start_id = RoomRunningDmsRef::firstItem([], ['id', 'desc'])->id;
    } elseif ($target == 'room_running_dms_sum') {
        $start_id = RoomRunningDmsSum::firstItem([], ['id', 'desc'])->id;
    } elseif ($target == 'room_running_sum') {
        $start_id = RoomRunningSum::firstItem([], ['id', 'desc'])->id;
    } else {
        $start_id = RoomRunning::firstItem([], ['id', 'desc'])->id;
    }

    $start_id = !empty($start_id) && $start_id > 0 ? intval($start_id) : 0;
    $end_id = $start_id + $maxlen;
    echo "\nget data target:{$target}, start_id:{$start_id}, end_id:{$end_id}, maxlen:{$maxlen}\n";

    foreach (range($start_id + 1, $end_id, $step) as $id_s) {
        $data_url = \app\Util::build_get($base_url, [
            'devtoken' => $token,
            'target' => $target,
            'id_s' => $id_s,
            'id_e' => $id_s + $step - 1,
        ]);
        $ret = \Tiny\Plugin\RpcHelper::curlRpc($data_url);
        $list = !empty($ret['list']) ? $ret['list'] : [];
        $buffer = [];
        echo "\nget data target:{$target}, id_s:{$id_s} num:" . count($list) . "\n";
        foreach ($list as $id => $item) {
            $buffer[] = $item;
            if (count($buffer) >= $buffer_len) {
                __saveRoomRunning($target, $buffer);
                $buffer = [];
            }
        }
        __saveRoomRunning($target, $buffer);
        if (count($list) == 0) {
            break;
        }
    }
}

function __saveRoomRunning($target, $buffer)
{
    if (empty($buffer)) {
        return;
    }

    try {
        if ($target == 'room_running_dms') {
            RoomRunningDms::tableBuilder()->insert($buffer);
        } elseif ($target == 'room_running_dms_ref') {
            RoomRunningDmsRef::tableBuilder()->insert($buffer);
        } elseif ($target == 'room_running_dms_sum') {
            RoomRunningDmsSum::tableBuilder()->insert($buffer);
        } elseif ($target == 'room_running_sum') {
            RoomRunningSum::tableBuilder()->insert($buffer);
        } else {
            RoomRunning::tableBuilder()->insert($buffer);
        }
        echo '.';
    } catch (Exception $ex) {
        echo 'X';
    }
}

function _syncRoomViewRecord($host, $token, $step, $maxlen, $buffer_len, $use_dms = 0)
{
    $base_url = "http://{$host}/api/HotFix/syncTableRoomViewRecord";

    $start_id = $use_dms ? RoomViewRecordDms::firstItem([], ['id', 'desc'])->id : RoomViewRecord::firstItem([], ['id', 'desc'])->id;
    $start_id = !empty($start_id) && $start_id > 0 ? intval($start_id) : 0;
    $end_id = $start_id + $maxlen;
    echo "\nget data use_dms:{$use_dms}, start_id:{$start_id}, end_id:{$end_id}, maxlen:{$maxlen}\n";

    foreach (range($start_id + 1, $end_id, $step) as $id_s) {
        $data_url = \app\Util::build_get($base_url, [
            'devtoken' => $token,
            'use_dms' => $use_dms,
            'id_s' => $id_s,
            'id_e' => $id_s + $step - 1,
        ]);
        $ret = \Tiny\Plugin\RpcHelper::curlRpc($data_url);
        $list = !empty($ret['list']) ? $ret['list'] : [];
        echo "\nget data use_dms:{$use_dms}, id_s:{$id_s} num:" . count($list) . "\n";

        $buffer = [];
        foreach ($list as $id => $item) {
            $buffer[] = $item;
            if (count($buffer) >= $buffer_len) {
                __saveRoomViewRecord($use_dms, $buffer);
                $buffer = [];
            }
        }
        __saveRoomViewRecord($use_dms, $buffer);
        if (count($list) == 0) {
            break;
        }
    }
}

function __saveRoomViewRecord($use_dms, $buffer)
{
    if (empty($buffer)) {
        return;
    }

    try {
        if ($use_dms) {
            RoomViewRecordDms::tableBuilder()->insert($buffer);
        } else {
            RoomViewRecord::tableBuilder()->insert($buffer);
        }
        echo '.';
    } catch (Exception $ex) {
        echo 'X';
    }
}

