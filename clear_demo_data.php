<?php

use app\api\RoomRecord;
use app\App;
use app\Util;
use Tiny\Plugin\DbHelper;
use Tiny\Plugin\LogHelper;

require_once(__DIR__ . '/vendor/autoload.php');
$app = \app\Boot::bootstrap(
    \app\App::app('app', require(__DIR__ . "/config/app-config.ignore.php"))
);


DbHelper::setOrmEventCallback(function ($type, $event) {
    if ($type == 'QueryExecuted') {
        $sql_str = Util::prepare_query($event->sql, $event->bindings);
        $date_str = date('Y-m-d H:i:s');
        echo "{$date_str} {$event->time}ms => {$sql_str} \n";
    }
});

$now = time();
$per_day = date('Ymd', $now - 2 * 24 * 3600);
$year = intval(date('Y'), $now);
$month = intval(date('m', $now));
$day = intval(date('d', $now));
$hour = intval(date('H', $now));
$minute = intval(date('i', $now));


echo "++++++++++++++++++++++++++++++++++++++\n";
echo "++++++++++++++++++++++++++++++++++++++\n";
echo "++++++++++++++++++++++++++++++++++++++\n";

$year = 2018;
$month = 6;

list($year, $month) = [intval($year), intval($month)];
$max_day = Util::max_days($year, $month);

$use_dms = App::config('services.countly.use_dms', false);
$countly_pre = App::config('ENV_WEB.countly_pre', 'steel');

function _crontabTaskLog($cls, $func, $rst)
{
    $log = LogHelper::create("crontab_{$cls}");
    $rst_str = json_encode($rst);
    $data_msg = "{$func}@{$rst_str}";
    $rst['code'] == 0 ? $log->info($data_msg) : $log->debug($data_msg);
}
$clearDays = 7;

$rst = RoomRecord::crontabClearRoomRunning($per_day, 'minute', $clearDays);
_crontabTaskLog('RoomRecord', 'crontabClearRoomRunning#minute', $rst);

$rst = RoomRecord::crontabClearRoomRunning($per_day, '5minute', $clearDays);
_crontabTaskLog('RoomRecord', 'crontabClearRoomRunning#5minute', $rst);

$rst = RoomRecord::crontabClearRoomRunning($per_day, 'hour', $clearDays);
_crontabTaskLog('RoomRecord', 'crontabClearRoomRunning#hour', $rst);

$rst = RoomRecord::crontabClearRoomRunning($per_day, 'day', $clearDays);
_crontabTaskLog('RoomRecord', 'crontabClearRoomRunning#day', $rst);

$rst = RoomRecord::crontabClearRoomViewRecord($per_day, $clearDays);
_crontabTaskLog('RoomRecord', 'crontabClearRoomViewRecord', $rst);

echo date('Y-m-d H:i:s') . ' RUN AT ' . date('Y-m-d') . ' END use:' . (time() - $now) . "s \n";
