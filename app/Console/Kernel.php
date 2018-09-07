<?php

namespace app\Console;

use app\Console\Commands\AddDmsLiveNotifyUrl;
use app\Console\Commands\AdminDailyViewCount;
use app\Console\Commands\CheckAdminExpirationDate;
use app\Console\Commands\ClearDataBase;
use app\Console\Commands\CountAdminAndRoomNum;
use app\Console\Commands\DeleteStreamVod;
use app\Console\Commands\FetchLiveMcsVod;
use app\Console\Commands\FetchRoomDmsNum;
use app\Console\Commands\FixRoomRunningDuplicates;
use app\Console\Commands\Inspire;
use app\Console\Commands\ReFetchAllMcsInfo;
use app\Console\Commands\RoomDailyViewCount;
use app\Console\Commands\RoomRunningToSum;
use app\Console\Commands\RoomViewerMax;
use app\Console\Commands\SaveDailyRunning;
use app\Console\Commands\SendXdyIsvNotify;
use app\Console\Commands\SumAdminViewNow;
use app\Console\Commands\SumRoomMinuteRunning5Minute;
use app\Console\Commands\SyncStreamLiveState;
use app\Console\Commands\XdyIsvAccountCredit;
use app\Console\Commands\XdyIsvOverOrder;
use app\Console\Commands\XdyIsvProductExpired;

class Kernel
{

    private static $schedule_map = [
        'Inspire' => ['hourly',],
        'SumAdminViewNow' => ['everyMinute',],
        'RoomRunningToSum' => ['everyMinute',],

        'CheckAdminExpirationDate' => ['everyMinute'],

        'FetchRoomDmsNum' => ['everyMinute',],

        'SendXdyIsvNotify' => ['everyMinute',],

        'RoomDailyViewCount' => ['perMinutes', 17],
        'RoomDailyViewCount#dailyAt' => ['dailyAt', '23:59'],

        'AdminDailyViewCount' => ['perMinutes', 17],
        'AdminDailyViewCount#dailyAt' => ['dailyAt', '23:59'],

        'ReFetchAllMcsInfo' => ['everyFiveMinutes'],

        'CountAdminAndRoomNum' => ['perMinutes', 23],

        'AddDmsLiveNotifyUrl' => ['perMinutes', 30],
        'RoomViewerMax' => ['perMinutes', 19],
        'FetchLiveMcsVod' => ['everyMinute'],

        'SumRoomMinuteRunning5Minute' => ['everyFiveMinutes',],

        'SaveDailyRunning' => ['perMinutes', 29],
        'SaveDailyRunning#dailyAt' => ['dailyAt', '23:59'],

        'FixRoomRunningDuplicates' => ['perMinutes', 17],

        'XdyIsvOverOrder' => ['dailyAt', '00:06'],
        'XdyIsvProductExpired' => ['dailyAt', '00:09'],  // 处理套餐失效 需要放到 计算套餐超出 之后

        'ClearDataBase' => ['dailyAt', '04:06'],

        'XdyIsvAccountCredit' => ['dailyAt', '09:09'],
        'DeleteStreamVod' => ['dailyAt', '04:09'],
        'SyncStreamLiveState' => ['perMinutes', 10],
    ];

    private static $_schedule_class_map = [];

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        FixRoomRunningDuplicates::class,    // ['perMinutes', 17],

        Inspire::class,          // ['hourly',],
        SendXdyIsvNotify::class,             // ['everyMinute',],

        CheckAdminExpirationDate::class,             // ['everyMinute',],

        ClearDataBase::class,            // ['dailyAt', '04:06'],
        SaveDailyRunning::class,             // ['everyTenMinutes',],   ['dailyAt', '23:59'],
        RoomDailyViewCount::class,           //  ['everyFiveMinutes',],   ['dailyAt', '23:59'],
        AdminDailyViewCount::class,   //  ['everyFiveMinutes',],   ['dailyAt', '23:59'],

        AddDmsLiveNotifyUrl::class,          // ['everyTenMinutes',],
        RoomViewerMax::class,   // ['everyTenMinutes',],

        XdyIsvAccountCredit::class,          // ['dailyAt', '09:06'],
        XdyIsvOverOrder::class,          //  ['dailyAt', '00:06'],
        XdyIsvProductExpired::class,             //  ['dailyAt', '00:07'],

        FetchRoomDmsNum::class,          // ['everyMinute',],
        SumAdminViewNow::class,          //  ['everyMinute',],
        RoomRunningToSum::class,             // ['everyMinute',],
        ReFetchAllMcsInfo::class,            //  ['everyFiveMinutes',],
        FetchLiveMcsVod::class,          //  ['everyMinute',],
        CountAdminAndRoomNum::class,             // ['everyTenMinutes',],
        SumRoomMinuteRunning5Minute::class,          //  ['everyFiveMinutes',],
        DeleteStreamVod::class,       //  ['dailyAt', '04:09'],
        SyncStreamLiveState::class,    //  ['perMinutes', 10],
    ];

    /**
     * 通过代码方式执行定时任务
     * @param int $ts 调用时的 时间戳 后端任务队列系统 保证每分钟 执行一次
     * @param bool $onlyCurrent
     * @param callable|null $log_callback
     * @return array 执行的结果
     * @throws \Exception
     */
    public static function runSchedule($ts, $onlyCurrent = true, callable $log_callback = null)
    {
        $now_min = intval(date('i', $ts));
        $count_min = intval($ts / 60);

        $hi = date('H:i', $ts);
        $day_hi = date('Y-m-d H:i', $ts);
        $ret = [];
        foreach (self::$schedule_map as $cmd => $item) {
            $cmd = explode('#', $cmd)[0];
            $s_type = $item[0];
            switch ($s_type) {
                case 'everyMinute':
                    $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    break;
                case 'hourly':
                    if ($now_min == 0) {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case 'daily':
                    if ($hi == '00:00') {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case 'perMinutes':
                    $num = !empty($item[1]) && $item[1] > 1 ? intval($item[1]) : 1;
                    if ($count_min % $num == 0) {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case 'everyFiveMinutes':
                    if ($count_min % 5 == 0) {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case 'everyTenMinutes':
                    if ($count_min % 10 == 0) {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case  'datetimeAt':
                    if (!empty($item[1]) && $day_hi == $item[1]) {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case  'datetimeAtRange':
                    if (!empty($item[1]) && !empty($item[2]) && $day_hi >= $item[1] && $day_hi <= $item[2]) {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                case 'dailyAt':
                    if (!empty($item[1]) && $hi == $item[1]) {
                        $ret[$cmd] = self::runScheduleSite($cmd, $ts, $onlyCurrent, $log_callback);
                    }
                    break;
                default:
                    throw new \Exception("error schedule type: {$s_type}");
                    break;
            }
        }
        return $ret;
    }

    public static function runScheduleSite($cmd, $timestamp, $onlyCurrent = true, callable $log_callback = null)
    {
        $log_list = [];
        $kernel = new static();
        foreach ($kernel->commands as $schedule_class) {
            $schedule = !empty(self::$_schedule_class_map[$schedule_class]) ? self::$_schedule_class_map[$schedule_class] : new $schedule_class();
            self::$_schedule_class_map[$schedule_class] = $schedule;
            if ($schedule->getName() == $cmd && $schedule instanceof SiteCommand) {
                $schedule->setLogHandle(function ($line, $tag) use (&$log_list, $log_callback) {
                    $log_list[] = date('Y-m-d H:i:s') . " [{$tag}] {$line}";
                    if (!empty($log_callback)) {
                        call_user_func_array($log_callback, [$line, $tag]);
                    }
                });
                if ($onlyCurrent) {
                    $schedule->handleCurrentSite($timestamp);
                } else {
                    $schedule->handle($timestamp);
                }
            }
        }
        return $log_list;
    }

    public static function listCommands()
    {
        $ret = [];
        $kernel = new static();
        foreach ($kernel->commands as $schedule_class) {
            /** @var SiteCommand $schedule */
            $schedule = !empty(self::$_schedule_class_map[$schedule_class]) ? self::$_schedule_class_map[$schedule_class] : new $schedule_class();
            self::$_schedule_class_map[$schedule_class] = $schedule;
            if ($schedule instanceof SiteCommand) {
                $ret[$schedule->getName()] = $schedule->getDoc();
            }
        }
        return $ret;
    }

    public static function listSchedules()
    {
        return self::$schedule_map;
    }

}
