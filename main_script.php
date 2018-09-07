<?php

use app\Http\Controllers\UploadController;
use app\Libs\AliOss;

require_once(__DIR__ . '/vendor/autoload.php');

\app\Boot::bootstrap(
    \app\App::app('app', require(__DIR__ . "/config/app-config.ignore.php"))
);




function main(){
    $redis = \app\AbstractClass::_getRedisInstance();

    while (true) {
        $data = $redis->blPop([UploadController::UPLOAD_VSS_KEY], 1);
        if (empty($data)) {
            echo '.';
            continue;
        }
        echo "\n==============================================";
        echo date('Y-m-d H:i:s') . ' redis data ' . print_r($data, true);
        if($data[0] == UploadController::UPLOAD_VSS_KEY){
            $params = json_decode($data[1], true);
            $rst = AliOss::uploadFileVss($params['filePath'], $params['fileName']);
            echo date('Y-m-d H:i:s') . ' uploadFileVss ret ' . print_r($rst, true);
        }
        echo "\n==============================================\n";
    }
}

main();