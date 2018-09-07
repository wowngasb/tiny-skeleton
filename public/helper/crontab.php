<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/28 0028
 * Time: 22:47
 */

/*
 * 定时任务 每分钟执行一次
 *
 */

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

ignore_user_abort(true);   //如果客户端断开连接，不会引起脚本abort.
set_time_limit(300);   //脚本执行延时上限 设置为5分钟

\app\Boot::bootstrap(
    \app\App::app('app', require(dirname(dirname(__DIR__)) . "/config/app-config.ignore.php"))
);

\app\Console\Kernel::runSchedule(time(), true, function ($line, $tag) {
    echo date('Y-m-d H:i:s') . " [{$tag}] {$line} \n";
});