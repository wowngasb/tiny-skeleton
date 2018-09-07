<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/10/25
 * Time: 9:36
 */

namespace app;

use app\api\Abstracts\Api;
use app\api\ContentMgr;
use app\api\GraphQL_\Enum\AdminTypeEnum;
use app\api\GraphQL_\Enum\StateEnum;
use app\api\GraphQL_\Enum\StreamTypeEnum;
use app\Console\Kernel;
use app\Libs\Send;
use app\Model\AdminUser;
use app\Model\LiveRoom;
use app\Model\PlayerBase;
use app\Model\SiteOpRecord;
use app\Model\StreamBase;
use app\Service\BlackApi;
use app\Service\CountlyApi;
use app\Service\MrqApi;
use app\Service\WsOpenApi;
use GuzzleHttp\Client as HttpClient;
use Tiny\OrmQuery\Q;
use Tiny\Util as _Util;

class Util extends _Util
{

    const MIN_DATE_PER_DAY = 20170101;

    /**
     * @param int $stime
     * @param int $etime
     * @param int $split_sec
     * @param array $fmt
     * @return array
     */
    public static function splitTimeRangeSec($stime, $etime, $split_sec, array $fmt = ['Y-m-d H:i:s', 'Y-m-d H:i:s'], $offset = 0)
    {
        if ($etime - $stime < $split_sec) {
            return [
                [date($fmt[0], $stime), date($fmt[1], $etime)]
            ];
        }
        list($stime, $etime) = [$stime - $offset, $etime - $offset];

        $range_arr = [];
        $time = $stime;
        while ($time < $etime) {
            $tmp = intval(($time + $split_sec) / $split_sec) * $split_sec;
            $tmp = $tmp >= $etime ? $etime : $tmp;

            $last = $tmp % $split_sec == 0 ? $tmp - 1 : $tmp;
            $range_arr[] = [date($fmt[0], $time + $offset), date($fmt[1], $last + $offset)];
            $time = $tmp;
        }
        return $range_arr;
    }


    public static function blackApi()
    {
        $dms_host = App::config('services.black.dms_host');
        $api_port = App::config('services.black.api_port');
        $srv = "http://{$dms_host}:{$api_port}/";
        $key = App::config('services.black.key');
        $api = new BlackApi($srv, $key);
        return $api;
    }

    public static function countlyApi()
    {
        $countly_srv = App::config('services.countly.srv', '');
        $countly_key = App::config('services.countly.key', '');
        $api = new CountlyApi($countly_srv, $countly_key);
        return $api;
    }

    public static function mrqApi()
    {
        $mrq_srv = App::config('services.mrq.mrq_srv');
        $mrq_auth = App::config('services.mrq.mrq_auth');
        $api = new MrqApi($mrq_srv, $mrq_auth);
        return $api;
    }

    public static function wsOpenApi()
    {
        $srv = App::config('services.openapi.srv');
        $key = App::config('services.openapi.key');
        $api = new WsOpenApi($srv, $key);
        return $api;
    }


    /**
     * @param $expiration_date
     * @return array
     */
    public static function whereByExpirationDate($expiration_date)
    {
        return self::whereByRange('expiration_date', $expiration_date);
    }

    public static function whereByIntervalTime($interval_time)
    {
        return self::whereByRange('interval_time', $interval_time);
    }

    /**
     * @param $created_at
     * @return array
     */
    public static function whereByCreatedAt($created_at)
    {
        return self::whereByRange('created_at', $created_at);
    }

    /**
     * @param $updated_at
     * @return array
     */
    public static function whereByUpdatedAt($updated_at)
    {
        return self::whereByRange('updated_at', $updated_at);
    }

    public static function whereByLoginTime($login_time)
    {
        return self::whereByRange('login_time', $login_time);
    }

    public static function whereByNumMax($num_max)
    {
        return self::whereByRange('num_max', $num_max, true);
    }

    public static function whereByPerDay($per_day)
    {
        return self::whereByRange('per_day', $per_day, true);
    }

    public static function whereByViewCount($view_count)
    {
        return self::whereByRange('view_count', $view_count, true);
    }

    /**
     * @param $key
     * @param $range
     * @param bool $as_int
     * @return array
     */
    public static function whereByRange($key, $range, $as_int = false)
    {
        return [
            "{$key}#between" => Q::whereBetween(Util::v($range, 'lower'), Util::v($range, 'upper'), function () use ($range, $as_int) {
                return Util::check_range($range, $as_int);
            }),
            "{$key}#gte-lower" => Q::where(Util::v($range, 'lower'), '>=', function () use ($range, $as_int) {
                list($lower, $upper) = Util::get_range($range, $as_int);
                return !empty($lower) && empty($upper);
            }),
            "{$key}#lte-lower" => Q::where(Util::v($range, 'upper'), '<=', function () use ($range, $as_int) {
                list($lower, $upper) = Util::get_range($range, $as_int);
                return !empty($upper) && empty($lower);
            }),
        ];
    }

    /**
     * @param $state
     * @return array
     */
    public static function whereByState($state)
    {
        return [
            'state#_eq' => Q::where($state, '=', function () use ($state) {
                return $state > 0 && $state != StateEnum::NOTDEL_VALUE;
            }),
            'state#_in' => Q::whereIn([StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE], function () use ($state) {
                return $state == StateEnum::NOTDEL_VALUE;
            }),
        ];
    }

    ##########################
    ######## 修复 相关 ########
    ##########################

    public static function _readTableItem($op_table, $op_prival, $autoTo = '')
    {
        $op_table = Util::trimlower($op_table);
        $model_cls = Util::v(Util::v(Model::$_op_record_map, $op_table, []), 'cls', '');
        $item = null;
        if (!empty($model_cls)) {
            $item = call_user_func_array("{$model_cls}::getOneById", [$op_prival]);
        }
        if (!empty($autoTo)) {
            $item = Util::try2array($item);
            unset($item['created_at'], $item['updated_at']);
            $del_keys = Util::v(Util::v(Model::$_op_record_map, $op_table, []), 'skip', []);
            foreach ($del_keys as $del_key) {
                unset($item[$del_key]);
            }
            if ($autoTo == 'json') {
                return !empty($item) ? json_encode($item) : '{}';
            } else if ($autoTo == 'array') {
                return !empty($item) ? $item : [];
            }
        }

        return $item;
    }

    public static function _fixSiteOpData(array $id_list, $action = 'rollback')
    {
        $ret = [];
        rsort($id_list);
        foreach ($id_list as $id) {
            if (SiteOpRecord::valueOneById($id, 'op_type') != SiteOpRecord::ENUM_OP_TYPE_UPDATE) {
                continue;
            }
            $op_table = SiteOpRecord::valueOneById($id, 'op_table');
            $op_prival = SiteOpRecord::valueOneById($id, 'op_prival');
            $op_prikey = SiteOpRecord::valueOneById($id, 'op_prikey');
            if ($action == 'rollback') {
                $last_str = SiteOpRecord::valueOneById($id, 'last_value');
                $lastVal = !empty($last_str) ? json_decode($last_str, true) : [];
                if (empty($lastVal) || empty($op_table)) {
                    continue;
                }
                unset($lastVal[$op_prikey]);
                $updateData = $lastVal;
            } else if ($action == 'redo') {
                $op_args_str = SiteOpRecord::valueOneById($id, 'op_args');
                $opArgs = !empty($op_args_str) ? json_decode($op_args_str, true) : [];
                if (empty($opArgs) || empty($op_table)) {
                    continue;
                }
                unset($opArgs[$op_prikey]);
                $updateData = $opArgs;
            } else if ($action == 'cancel') {
                $this_str = SiteOpRecord::valueOneById($id, 'this_value');
                $thisVal = !empty($this_str) ? json_decode($this_str, true) : [];
                if (empty($thisVal) || empty($op_table)) {
                    continue;
                }
                unset($thisVal[$op_prikey]);
                $updateData = $thisVal;
            } else {
                continue;
            }

            $op_table = Util::trimlower($op_table);
            $model_cls = Util::v(Util::v(Model::$_op_record_map, $op_table, []), 'cls', '');
            if (!empty($model_cls) && !empty($updateData)) {
                call_user_func_array("{$model_cls}::setOneById", [$op_prival, $updateData]);
            }

            $ret[] = [
                'action' => $action,
                'updateData' => $updateData,
                'op_table' => $op_table,
                'op_prival' => $op_prival,
                'op_prikey' => $op_prikey,
            ];
        }
        return $ret;
    }


    public static function update_admin_sub($admin_id)
    {
        if (Api::_isAgent($admin_id)) {
            Util::map_all_super(function ($admin_id) {
                Util::agentSub($admin_id, -1);
            });
        } elseif (Api::_isParent($admin_id)) {
            Util::parentSub(AdminUser::agent_id($admin_id), -1);
        } elseif (Api::_isSub($admin_id)) {
            Util::subSub(AdminUser::parent_id($admin_id), -1);
        }
    }

    public static function map_all_super($func)
    {
        AdminUser::tableBuilderEx([
            'admin_type' => AdminTypeEnum::SUPER_VALUE
        ])->chunk(100, function ($list) use ($func) {
            foreach ($list as $item) {
                $admin_id = self::v($item, 'admin_id', 0);
                if (!empty($admin_id)) {
                    $func($admin_id);
                }
            }
        });
    }

    public static function xdyAgentSub($timeCache = 30)
    {
        return self::_cacheDataManager(__METHOD__, self::_hashKey(), function () {
            return Api::_getAllAdminWithSlug(AdminUser::SLUG_AGENT, AdminTypeEnum::AGENT_VALUE);
        }, function ($data) {
            return !empty($data) ? true : 5;
        }, $timeCache);
    }

    public static function xdyParentSub($timeCache = 30)
    {
        return self::_cacheDataManager(__METHOD__, self::_hashKey(), function () {
            return Api::_getAllAdminWithSlug(AdminUser::SLUG_PARENT, AdminTypeEnum::PARENT_VALUE);
        }, function ($data) {
            return !empty($data) ? true : 5;
        }, $timeCache);
    }

    public static function agentSub($admin_id, $timeCache = 600)
    {
        return self::_cacheDataManager(__METHOD__, self::_hashKey(['a' => $admin_id]), function () use ($admin_id) {
            return AdminUser::_get(AdminUser::tableBuilderEx([
                'state' => [StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE],
                'admin_type' => AdminTypeEnum::AGENT_VALUE,
            ]), ['*'], 0, 200);
        }, function ($data) {
            return !empty($data) ? true : 5;
        }, $timeCache);
    }

    public static function parentSub($admin_id, $timeCache = 600)
    {
        return self::_cacheDataManager(__METHOD__, self::_hashKey(['a' => $admin_id]), function () use ($admin_id) {
            return AdminUser::_get(AdminUser::tableBuilderEx([
                'state' => [StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE],
                'agent_id' => $admin_id,
                'admin_type' => AdminTypeEnum::PARENT_VALUE,
            ]), ['*'], 0, 200);
        }, function ($data) {
            return !empty($data) ? true : 5;
        }, $timeCache);
    }

    public static function subSub($admin_id, $timeCache = 600)
    {
        return self::_cacheDataManager(__METHOD__, self::_hashKey(['a' => $admin_id]), function () use ($admin_id) {
            return AdminUser::_get(AdminUser::tableBuilderEx([
                'state' => [StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE],
                'parent_id' => $admin_id,
                'admin_type' => AdminTypeEnum::SUB_VALUE,
            ]), ['*'], 0, 200);
        }, function ($data) {
            return !empty($data) ? true : 5;
        }, $timeCache);
    }

    public static function roomSub($admin_id, $timeCache = 600)
    {
        return self::_cacheDataManager(__METHOD__, self::_hashKey(['a' => $admin_id]), function () use ($admin_id) {
            if (Api::_isParent($admin_id)) {
                return LiveRoom::_get(LiveRoom::tableBuilderEx([
                    'state' => [StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE],
                    'admin_id' => $admin_id,
                ]), ['*'], 0, 200);
            } elseif (Api::_isSub($admin_id)) {
                $room_id_set = Api::_loadACLByAdmin($admin_id, 'room');
                return LiveRoom::_get(LiveRoom::tableBuilderEx([
                    'state' => [StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE],
                    'admin_id' => AdminUser::parent_id($admin_id),
                    'room_id' => $room_id_set,
                ]), ['*'], 0, 200);
            }
            return [];
        }, function ($data) {
            return !empty($data) ? true : 5;
        }, $timeCache);
    }

    public static function streamSub($admin_id, $timeCache = 600)
    {
        return self::_cacheDataManager(__METHOD__, self::_hashKey(['a' => $admin_id]), function () use ($admin_id) {
            if (Api::_isParent($admin_id)) {
                return StreamBase::_get(StreamBase::tableBuilderEx([
                    'state' => [StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE],
                    'admin_id' => $admin_id,
                    'stream_type' => StreamTypeEnum::STREAM_MCS_VALUE,
                ]), ['*'], 0, 200);
            } elseif (Api::_isSub($admin_id)) {
                return StreamBase::_get(StreamBase::tableBuilderEx([
                    'state' => [StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE],
                    'admin_id' => AdminUser::parent_id($admin_id),
                    'stream_type' => StreamTypeEnum::STREAM_MCS_VALUE,
                ]), ['*'], 0, 200);
            }
            return [];
        }, function ($data) {
            return !empty($data) ? true : 5;
        }, $timeCache);
    }

    public static function playerSub($admin_id, $timeCache = 600)
    {
        return self::_cacheDataManager(__METHOD__, self::_hashKey(['a' => $admin_id]), function () use ($admin_id) {
            if (Api::_isParent($admin_id)) {
                return PlayerBase::_get(PlayerBase::tableBuilderEx([
                    'state' => [StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE],
                    'admin_id' => $admin_id,
                ]), ['*'], 0, 200);
            } elseif (Api::_isSub($admin_id)) {
                return PlayerBase::_get(PlayerBase::tableBuilderEx([
                    'state' => [StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE],
                    'admin_id' => AdminUser::parent_id($admin_id),
                ]), ['*'], 0, 200);
            }
            return [];
        }, function ($data) {
            return !empty($data) ? true : 5;
        }, $timeCache);
    }

    /**
     * @param $email
     * @param $subject
     * @param $content
     * @return string  空字符串 表示 发送成功  非空表示发送失败的原因
     */
    public static function sendEmail($email, $subject, $content)
    {
        $emailHost = App::config('services.email.emailHost');
        $emailPort = App::config('services.email.emailPort');
        $emailUsername = App::config('services.email.emailUsername');
        $emailPassword = App::config('services.email.emailPassword');
        $emailFromName = App::config('services.email.emailFromName');
        $emailFrom = App::config('services.email.emailFrom');

        $api = new Send($emailHost, $emailPort, $emailUsername, $emailPassword, $emailFromName, $emailFrom);
        return $api->sendEmail($email, $subject, $content);
    }

    public static function publish($topic, $data)
    {
        $api = self::blackApi();
        return $api->publish($topic, $data);
    }

    public static function allDepsInObj($deps, $obj)
    {
        if (empty($deps)) {
            return true;
        }

        $deps = self::build_map_set($deps);
        foreach ($deps as $dep) {
            if (empty($obj[$dep])) {
                return false;
            }
        }
        return true;
    }

    public static function parentDepsFromMenu($depList, $objMenu)
    {
        $deps = [];
        foreach ($objMenu as $menu) {
            $access_value = Util::v($menu, 'access_value', '');
            if (!empty($menu['dep']) && in_array($access_value, $depList)) {
                $deps = array_merge($menu['dep']);
            }
            $children = Util::v($menu, 'children', []);
            foreach ($children as $child) {
                $_access_value = Util::v($child, 'access_value', '');
                if (!empty($child['dep']) && in_array($_access_value, $depList)) {
                    $deps = array_merge($child['dep']);
                }
            }
        }
        return Util::build_map_set(array_map(function ($v) {
            return join('.', $v);
        }, $deps));
    }

    ##########################
    ######## CMD 相关 ########
    ##########################

    public static function cmd_commands($args, $print)
    {
        false && func_get_args();
        $s = microtime(true);
        $ret = Kernel::listCommands();
        $used = intval((microtime(true) - $s) * 1000) / 1000;
        foreach ($ret as $cmd => $doc) {
            $print && $print("{$cmd} => {$doc}");
        }
        return <<<EOT
####################################
list_commands: {$used}s;
EOT;
    }

    public static function cmd_schedules($args, $print)
    {
        false && func_get_args();
        $s = microtime(true);
        $ret = Kernel::listSchedules();
        foreach ($ret as $cmd => $item) {
            $print && $print("{$cmd} => " . json_encode($item));
        }
        $used = intval((microtime(true) - $s) * 1000) / 1000;
        return <<<EOT
####################################
list_schedules: {$used}s;
EOT;
    }

    /**
     * @param $args
     * @param $print
     * @return string
     * @throws \Exception
     */
    public static function cmd_crontab($args, $print)
    {
        $s = microtime(true);
        $ts = !empty($args[2]) && intval($args[2]) > 0 ? intval($args[2]) : time();
        $ret = Kernel::runSchedule($ts, false, function ($line, $tag) use ($print) {
            $print && $print("[{$tag}] {$line}");
        });
        $ret && $ret;
        $used = intval((microtime(true) - $s) * 1000) / 1000;
        return <<<EOT
####################################
crontab: {$used}s;
EOT;
    }

    public static function cmd_run($args, $print)
    {
        $s = microtime(true);
        $cmd = !empty($args[2]) ? strval($args[2]) : '';
        $ts = !empty($args[3]) && intval($args[3]) > 0 ? intval($args[3]) : time();

        $ret = Kernel::runScheduleSite($cmd, $ts, false, function ($line, $tag) use ($print) {
            $print && $print("[{$tag}] {$line}");
        });
        $ret && $ret;
        $used = intval((microtime(true) - $s) * 1000) / 1000;
        return <<<EOT
####################################
####################################
commands: {$used}s;
EOT;
    }

    public static function cmd_clear($args, $print)
    {
        false && func_get_args();
        $s = microtime(true);

        $type = !empty($args[2]) ? trim($args[2]) : 'route|console';

        if (stripos($type, 'view') !== false) {
            $view_path = Controller::getViewCachePath();
            $view_list = static::getfiles($view_path);
            foreach ($view_list as $view_cache => $v_name) {
                if (!static::str_startwith($v_name, '.') && !empty($view_cache) && is_file($view_cache)) {
                    $print && $print("delete View Cache {$v_name}");
                    unlink($view_cache);
                }
            }
        }

        if (stripos($type, 'route') !== false) {
            $route_path = Boot::getRouteCachePath();
            $route_list = static::getfiles($route_path);
            foreach ($route_list as $route_cache => $r_name) {
                if (!static::str_startwith($r_name, '.') && !empty($route_cache) && is_file($route_cache)) {
                    $print && $print("delete Route Cache {$r_name}");
                    unlink($route_cache);
                }
            }
        }

        if (stripos($type, 'console') !== false) {
            $console_file = Boot::getConsoleStorageFile();
            if (!empty($console_file) && is_file($console_file)) {
                $print && $print("delete Console Storage File => {$console_file} ");
                unlink($console_file);
            }
        }
        $used = intval((microtime(true) - $s) * 1000) / 1000;
        return <<<EOT
####################################
####################################
clear: {$used}s;
EOT;
    }

    public static function cmd_version($args, $print)
    {
        false && func_get_args();
        $root_path = App::app()->path();
        $ver = static::load_git_ver($root_path);
        return <<<EOT
Git Info:
root_path => {$root_path}
git_ref   => {$ver['git_ref']}
ref_type  => {$ver['ref_type']}
git_ver   => {$ver['git_ver']}
EOT;
    }


    public static function dev_host($default_host = '')
    {
        $dev_host = App::config('ENV_WEB.devsrv');
        $dev_srv = App::config('app.dev_srv', '');
        if (!empty($dev_srv)) {
            $dev_host = $dev_srv;
        }

        if (empty($dev_host)) {
            $dev_host = $default_host;
        }
        $dev_host = Util::stri_startwith($dev_host, 'https://') || Util::stri_startwith($dev_host, 'http://') ? $dev_host : "http://{$dev_host}";
        $dev_host = Util::str_endwith('/', $dev_host) ? $dev_host : "{$dev_host}/";
        return $dev_host;
    }

    public static function tryAddHost($host, $path, array $args = [])
    {
        if (empty($path)) {
            return '';
        }

        $host = self::stri_startwith($host, 'http://') || self::stri_startwith($host, 'https://') ? $host : "http://{$host}";
        $host = self::str_endwith($host, '/') ? $host : "{$host}/";

        $path = self::str_startwith($path, '/') ? substr($path, 1) : $path;
        $uri = self::stri_startwith($path, 'http://') || self::stri_startwith($path, 'https://') ? $path : "{$host}{$path}";
        if (!empty($args)) {
            $uri = self::build_get($uri, $args);
        }
        return $uri;
    }

    public static function getCdn($admin_id = 0)
    {
        $admin_id = $admin_id > 0 ? intval($admin_id) : 0;
        $cdn = App::config('services.common_cdn');
        if (!empty($admin_id)) {
            $cname_cdn = AdminUser::cname_cdn($admin_id);
            if (!empty($cname_cdn)) {
                $cdn = $cname_cdn;
            } else {
                $agent_id = AdminUser::agent_id($admin_id);
                $cname_cdn = AdminUser::cname_cdn($agent_id);
                $cdn = !empty($cname_cdn) ? $cname_cdn : $cdn;
            }
        }

        if (!self::stri_startwith($cdn, 'http://') && !self::stri_startwith($cdn, 'https://')) {
            $cdn = "http://{$cdn}";
        }
        $assets_ver = App::config('ENV_WEB.ver');
        $assets_ver = !empty($assets_ver) ? $assets_ver : App::config('services.cdn_ver');
        $webver = $assets_ver;
        return [Util::trimlower($cdn), $webver];
    }

    public static function topN(array $arr, $top, $cmp = 1)
    {
        if (empty($arr)) {
            return [];
        }
        uasort($arr, function ($a, $b) use ($cmp) {
            return $a == $b ? 0 : ($a < $b ? $cmp : -$cmp);
        });

        if ($top <= 0 || $top >= count($arr)) {
            return $arr;
        }

        $top_map = [];
        foreach ($arr as $k => $v) {
            $top_map[$k] = $v;
            if (count($top_map) == $top) {
                return $top_map;
            }
        }
        return $arr;
    }

    public static function device_type()
    {
        $_SERVER['HTTP_USER_AGENT'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        //全部变成小写字母
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $type = 'other';
        //分别进行判断
        if (strpos($agent, 'iphone')) {
            $type = 'ios';
        }
        if (strpos($agent, 'ipad')) {
            $type = 'ipad';
        }
        if (strpos($agent, 'android')) {
            $type = 'android';
        }
        return $type;
    }

    public static function is_weixin()
    {
        $_SERVER['HTTP_USER_AGENT'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $is_weixin = false;
        if (!empty($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            $is_weixin = true;

        }
        return $is_weixin;
    }

    public static function content($name, $filed = 'content_text', $default = null, $admin_id = 0, $room_id = 0)
    {
        return ContentMgr::_content($name, $filed, $default, $admin_id, $room_id);
    }

    public static function page_seo($title = '', $description = null, $keywords = null)
    {
        $title = trim("{$title}");
        return [
            'title' => $title,
            'description' => !is_null($description) ? trim("{$description}") : $title,
            'keywords' => !is_null($keywords) ? trim("{$keywords}") : $title,
        ];
    }


    /**
     * @param $phone_num
     * @param $msg
     * @return string  空字符串 表示 发送成功  非空表示发送失败的原因
     */
    public static function yunPianSms($phone_num, $msg)
    {
        $apikey = App::config('services.yunpian.key', '');;
        if (empty($apikey)) {
            return false;
        }
        $url = 'http://yunpian.com/v1/sms/send.json';
        $encoded_text = urlencode("{$msg}");
        $mobile = urlencode("{$phone_num}");
        $post_string = "apikey=$apikey&text=$encoded_text&mobile=$mobile";

        $res_str = self::sock_post($url, $post_string);
        $ret = !empty($res_str) ? json_decode($res_str, true) : [];

        $log_msg = "yunPianSms phone_num:{$phone_num}, msg:{$msg}, ret:{$res_str}";
        if (!empty($ret) && $ret['code'] == 0) {
            self::debug($log_msg, __METHOD__, __CLASS__, __LINE__);
            return '';
        } else {
            self::error($log_msg, __METHOD__, __CLASS__, __LINE__);
            return !empty($ret['msg']) ? $ret['msg'] : '未知原因';
        }
    }

    /**
     * @param string $url 服务的url地址
     * @param string $query 请求串.
     * @return string
     */
    private static function sock_post($url, $query)
    {
        $data = '';
        $info = parse_url($url);
        $fp = fsockopen($info['host'], 80, $errno, $errstr, 30);
        if (!$fp) {
            return $data;
        }
        $head = 'POST ' . $info['path'] . " HTTP/1.0\r\n";
        $head .= 'Host: ' . $info['host'] . "\r\n";
        $head .= 'Referer: http://' . $info['host'] . $info['path'] . "\r\n";
        $head .= "Content-type: application/x-www-form-urlencoded\r\n";
        $head .= 'Content-Length: ' . strlen(trim($query)) . "\r\n";
        $head .= "\r\n";
        $head .= trim($query);
        fputs($fp, $head);
        $header = '';
        while ($str = trim(fgets($fp, 4096))) {
            $header .= $str;
        }
        while (!feof($fp)) {
            $data .= fgets($fp, 4096);
        }

        return $data;
    }

    /**
     * ip 地域查询
     * @param string $ip
     * @param int $timeCache
     * @return string
     */
    public static function getIpLocation($ip, $timeCache = 36000)
    {
        if (empty($ip)) {
            return '未知';
        }
        if (Util::stri_cmp($ip, '127.0.0.1') || Util::stri_cmp($ip, '0.0.0.0')) {
            return '本机地址';
        }

        return self::_cacheDataManager(__METHOD__, self::_hashKey(['ip' => $ip]), function () use ($ip) {
            try {
                $client = new HttpClient(['timeout' => 1]);
                $token = App::config('services.ipip.token');
                $server = App::config('services.ipip.srv');

                $response = $client->request('GET', "{$server}/find?addr={$ip}", [
                    'headers' => [
                        'Token' => $token
                    ]
                ]);
                $body = $response->getBody()->getContents();

                $result = json_decode($body, true);

                $log_msg = "ip:{$ip}, result:" . json_encode($result);
                self::debug($log_msg, __METHOD__, __CLASS__, __LINE__);

                if ($result['ret'] == 'ok') {
                    return "{$result['data'][0]} {$result['data'][1]} {$result['data'][2]} {$result['data'][3]} {$result['data'][4]}";
                }

            } catch (\Exception $e) {
                $log_msg = "ip地域查询错误, ip: {$ip}, error: " . $e->getMessage();
                self::error($log_msg, __METHOD__, __CLASS__, __LINE__);
            }
            return '未知';
        }, function ($data) {
            return $data != '未知' ? true : 5;
        }, $timeCache);
    }

    public static function sendEmailTemplate($to, $sub, $tpl, $subject)
    {
        false && func_get_args();
        return [
            'result' => 1,
        ];
    }

    public static function tryAddCdnAuthKey($url)
    {
        $urlArr = self::_parseVideoUrl($url);
        if (empty($urlArr['videoType']) || ($urlArr['videoType'] != 'rtmp' && $urlArr['videoType'] != 'hls' && $urlArr['videoType'] != 'flv')) {
            return $url;
        }
        list($app, $stream_uri) = [$urlArr['app'], $urlArr['stream_uri']];
        $f_stream = self::_addPubAuthKey($app, $stream_uri);
        return "{$urlArr['schema']}://{$urlArr['domain']}/{$app}/{$f_stream}";

    }

    public static function _parseVideoUrl($url)
    {
        $ret = [];
        $url = trim($url);
        $idx = strpos($url, '://');
        $ret['schema'] = $idx > 0 ? trim(substr($url, 0, $idx)) : 'http';
        if ($idx > 0) {
            $url = substr($url, $idx + 3);
        }

        $a_idx = strpos($url, '/');
        $ret['domain'] = $a_idx > 0 ? trim(substr($url, 0, $a_idx)) : trim($url);
        if ($a_idx > 0) {
            $url = substr($url, $a_idx + 1);
            $tmpArr = explode('/', $url, 2);
            $ret['app'] = !empty($tmpArr[0]) ? trim($tmpArr[0]) : '';
            $ret['stream_uri'] = !empty($tmpArr[1]) ? trim($tmpArr[1]) : '';
        }
        if (!empty($ret['stream_uri'])) {
            $tmpArr = explode('?', $ret['stream_uri'], 2);
            $ret['base'] = !empty($tmpArr[0]) ? trim($tmpArr[0]) : '';
            $ret['query'] = !empty($tmpArr[1]) ? trim($tmpArr[1]) : '';
            if (!empty($ret['base'])) {
                $tmp_args = explode('/', $ret['base']);
                $file = array_pop($tmp_args);
                $c_idx = strrpos($file, '.');
                if ($c_idx > 0) {
                    $ret['stream'] = substr($file, 0, $c_idx);
                    $ret['ext'] = substr($file, $c_idx + 1);
                } else {
                    $ret['stream'] = $file;
                    $ret['ext'] = '';
                }
            }
        }
        if (!empty($ret['app']) && !empty($ret['stream'])) {
            if ($ret['schema'] == 'rtmp') {
                $ret['videoType'] = 'rtmp';
            } else if ($ret['schema'] == 'http' && $ret['ext'] == 'm3u8') {
                $ret['videoType'] = 'hls';
            } else if ($ret['schema'] == 'http' && $ret['ext'] == 'flv') {
                $ret['videoType'] = 'flv';
            }
        }
        return $ret;
    }

    private static function _addPubAuthKey($app, $stream)
    {
        $idx = strpos($stream, '?');
        $base_stream = $idx > 0 ? substr($stream, 0, $idx) : $stream;
        $cdnKey = App::config('services.cdn_pri_key');
        $timestamp = time() + 24 * 3600;
        $sstring = "/{$app}/{$base_stream}-{$timestamp}-0-0-{$cdnKey}";
        $md5hash = md5($sstring);
        $auth_key = "{$timestamp}-0-0-{$md5hash}";
        $o_stream = strpos($stream, '?') > 0 ? $stream : "{$stream}?";
        $o_stream = (substr($o_stream, -1) == '?' || substr($o_stream, -1) == '&') ? $o_stream : "{$o_stream}&";
        $o_stream = "{$o_stream}auth_key={$auth_key}";
        return $o_stream;
    }

    public static function getText($strContent)
    {
        return preg_replace("/<[^>]+>/is", "", $strContent);
    }

}