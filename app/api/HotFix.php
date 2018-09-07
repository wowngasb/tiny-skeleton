<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/10/30
 * Time: 15:20
 */

namespace app\api;


use app\api\Abstracts\AdminApi;
use app\api\GraphQL_\Enum\AdminTypeEnum;
use app\api\GraphQL_\Enum\LiveStateEnum;
use app\api\GraphQL_\Enum\StateEnum;
use app\api\GraphQL_\Enum\StreamTypeEnum;
use app\App;
use app\Boot;
use app\Exception\ApiAuthError;
use app\Exception\ApiParamsError;
use app\Libs\AdminAuth;
use app\Libs\IpAddrHelper;
use app\Model\AdminAccessControl;
use app\Model\AdminUser;
use app\Model\DailyRoomRunning;
use app\Model\DailyRoomRunningDms;
use app\Model\LiveRoom;
use app\Model\PlayerBase;
use app\Model\RoomRunning;
use app\Model\RoomRunningDms;
use app\Model\RoomRunningDmsRef;
use app\Model\RoomRunningDmsSum;
use app\Model\RoomRunningSum;
use app\Model\RoomViewRecord;
use app\Model\RoomViewRecordDms;
use app\Model\SiteMgrUser;
use app\Model\StreamBase;
use app\Model\StreamMcs;
use app\Util;
use Tiny\OrmQuery\Q;
use Tiny\Plugin\DbHelper;
use Tiny\Plugin\DevAuthController;

class HotFix extends AdminApi
{

    ################################################################
    ###########################  beforeAction ##########################
    ################################################################

    public function test()
    {
        $api = DataAnalysis::_createFromApi($this);
        $ret = $api->groupAgentRunning(100);
        return $ret;
    }

    ################################################################
    ###########################  beforeAction ##########################
    ################################################################


    /**
     * @throws ApiAuthError
     * @throws \Tiny\Exception\AppStartUpError
     */
    private function _checkDevelop()
    {
        if (!DevAuthController::_checkDevelopKey($this->getRequest())) {
            throw new ApiAuthError('must auth as develop');
        }
    }

    /**
     * @param array $params
     * @return array
     * @throws ApiAuthError
     * @throws \Tiny\Exception\AppStartUpError
     */
    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);

        $this->_checkDevelop();
        return $params;
    }

    ################################################################
    ###########################  redis 清理函数 ##########################
    ################################################################

    /**
     * 删除 redis 缓存数据
     * @param string $prefix 前缀
     * @param string $pattern 通配符
     * @return array ['keys' => $keys];
     * @throws ApiParamsError
     */
    public function removeBMCache($prefix, $pattern = '')
    {
        if (empty($prefix)) {
            throw new ApiParamsError('参数错误');
        }
        $redis = self::_getRedisInstance();
        $keys = $redis->keys("{$prefix}:{$pattern}");
        foreach ($keys as $key) {
            $redis->del($key);
        }
        return [
            'keys' => $keys
        ];
    }


    ################################################################
    ###########################  数据清理函数 ##########################
    ################################################################

    public function rmMcsAccount($stream_id)
    {
        if (!StreamMcs::checkOne($stream_id)) {
            throw new ApiParamsError('参数错误');
        }

        $mcs_id = StreamMcs::mcs_id($stream_id);
        $mcs_ret = StreamMcs::delOneById($stream_id);
        $base_ret = StreamBase::delOneById($stream_id);
        $room_ret = LiveRoom::_update(LiveRoom::tableBuilderEx([
            'stream_id' => $stream_id
        ]), [
            'stream_id' => 0
        ]);
        $api_ret = $mcs_id > 0 ? Util::wsOpenApi()->delMcs($mcs_id) : [];
        return [
            'stream_id' => $stream_id,
            'mcs_ret' => $mcs_ret,
            'base_ret' => $base_ret,
            'room_ret' => $room_ret,
            'api_ret' => $api_ret,
        ];
    }

    public function rmAllDeletedMcsAccount()
    {
        $delMcsDict = StreamBase::dictItem([
            'state' => StateEnum::DELETED_VALUE,
            'stream_type' => StreamTypeEnum::STREAM_MCS_VALUE
        ]);

        $info = [];
        foreach ($delMcsDict as $stream_id => $mcs) {
            $info[] = $this->rmMcsAccount($stream_id);
        }
        return [
            'info' => $info
        ];
    }


    ################################################################
    ###########################  数据同步函数 ##########################
    ################################################################

    /**
     * 同步数据  拉取  room_view_record  room_view_record_dms  数据表 数据
     * @param int $id_s id 起始
     * @param int $id_e id 结束
     * @param int $use_dms 是否使用 room_view_record_dms
     * @return array
     * @throws ApiParamsError
     */
    public function syncTableRoomViewRecord($id_s, $id_e, $use_dms = 0)
    {
        list($id_s, $id_e) = [intval($id_s), intval($id_e)];
        if ($id_e <= $id_s || $id_e - $id_s > 5000) {
            throw new ApiParamsError('参数错误');
        }
        $use_dms = !empty($use_dms) ? 1 : 0;
        if ($use_dms) {
            $list = RoomViewRecordDms::dictItem([
                'id' => Q::whereBetween($id_s, $id_e)
            ]);
        } else {
            $list = RoomViewRecord::dictItem([
                'id' => Q::whereBetween($id_s, $id_e)
            ]);
        }
        return [
            'list' => $list
        ];
    }

    /**
     * 同步数据  拉取  room_running 系列数据表  数据表 数据
     * @param int $id_s id 起始
     * @param int $id_e id 结束
     * @param string $target 目标数据表
     * @return array
     * @throws ApiParamsError
     */
    public function syncTableRoomRunning($id_s, $id_e, $target = 'room_running')
    {
        list($id_s, $id_e) = [intval($id_s), intval($id_e)];
        if ($id_e <= $id_s || $id_e - $id_s > 5000) {
            throw new ApiParamsError('参数错误');
        }

        if ($target == 'room_running_dms') {
            $list = RoomRunningDms::dictItem([
                'id' => Q::whereBetween($id_s, $id_e)
            ]);
        } elseif ($target == 'room_running_dms_ref') {
            $list = RoomRunningDmsRef::dictItem([
                'id' => Q::whereBetween($id_s, $id_e)
            ]);
        } elseif ($target == 'room_running_dms_sum') {
            $list = RoomRunningDmsSum::dictItem([
                'id' => Q::whereBetween($id_s, $id_e)
            ]);
        } elseif ($target == 'room_running_sum') {
            $list = RoomRunningSum::dictItem([
                'id' => Q::whereBetween($id_s, $id_e)
            ]);
        } else {
            $list = RoomRunning::dictItem([
                'id' => Q::whereBetween($id_s, $id_e)
            ]);
        }
        return [
            'list' => $list
        ];
    }

    ################################################################
    ###########################  定时任务函数 ##########################
    ################################################################

    /**
     * 执行定时任务 crontabXdyIsvAdminProductExpiredDays
     * 处理 用户套餐 过期 信息
     * @param int $per_day 日期 格式为 20170101
     * @return array
     */
    public function runCrontabXdyIsvAdminProductExpiredDays($per_day)
    {
        return XdyIsvMgr::crontabXdyIsvAdminProductExpiredDays($per_day);
    }

    /**
     * 执行定时任务 crontabReFetchAllMacsInfo
     * 同步所有 MCS 直播帐号 信息
     * @return array
     */
    public function runCrontabReFetchAllMacsInfo()
    {
        return StreamMgr::crontabReFetchAllMcsInfo();
    }

    /**
     * 执行定时任务 crontabReFetchAllMacsInfo  按日期 执行
     * @param int $per_day 日期 格式为 20170101
     * @return array
     * @throws ApiParamsError
     */
    public function runCrontabXdyIsvAdminLimitOnlineNumberOverOrderPerDay($per_day)
    {
        return XdyIsvMgr::crontabXdyIsvAdminLimitOnlineNumberOverOrder($per_day);
    }

    /**
     * 执行定时任务 crontabReFetchAllMacsInfo  按月份 执行
     * @param int $year
     * @param int $month
     * @return array
     * @throws ApiParamsError
     */
    public function runCrontabXdyIsvAdminLimitOnlineNumberOverOrderYm($year, $month)
    {
        list($year, $month) = [intval($year), intval($month)];
        $max_day = Util::max_days($year, $month);
        if ($year <= 0 || $month <= 0 || $max_day <= 0) {
            throw new ApiParamsError("args error year:{$year}, month:{$month}");
        }

        $rst = [];
        for ($idx = 1; $idx <= $max_day; $idx++) {
            $_day = $idx < 10 ? "0{$idx}" : "{$idx}";
            $_month = $month < 10 ? "0{$month}" : "{$month}";
            $per_day = intval("{$year}{$_month}{$_day}");
            if ($per_day >= intval(date('Ymd'))) {
                break;
            }
            $rst[] = XdyIsvMgr::crontabXdyIsvAdminLimitOnlineNumberOverOrder($per_day);
        }
        return $rst;
    }

    /**
     * 执行定时任务 crontabCountAdminNum  计算 客户实时在线人数
     * @return array
     */
    public function runCrontabCountAdminNum()
    {
        $now = time();
        $rst = RoomRecord::crontabCountAdminNum($now, 30 * 24 * 3600);
        return [
            'rst' => $rst
        ];
    }

    /**
     * 执行定时任务  crontabRoomDailyViewCount  计算频道每日峰值数据
     * daily_view_count 数据表
     * @param int $per_day 日期 格式为 20170101
     * @return array
     */
    public function runCrontabRoomDailyViewCount($per_day = 0)
    {
        $per_day = !empty($per_day) ? intval($per_day) : intval(date('Ymd'));
        // 统计每日数据 需要放到 最后 依次尝试读取 统计数据
        $rst = RoomRecord::crontabRoomDailyViewCount($per_day);
        return [
            'rst' => $rst
        ];
    }

    ################################################################
    ###########################  检查并发设置 ##########################
    ################################################################

    /**
     * 批量处理 菜单权限 目标  自营客户
     * @param string $admin_slug
     * @return array
     */
    public function allVLimitAllRoomUsedForParentBySlug($admin_slug = AdminUser::SLUG_PARENT)
    {
        $adminDict = AdminUser::dictItem([
            'admin_slug' => Q::where($admin_slug, '=', function () use ($admin_slug) {
                return !empty($admin_slug);
            }),
            'state' => [StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE],
            'admin_type' => AdminTypeEnum::PARENT_VALUE,
        ]);
        $info = [];
        foreach ($adminDict as $parent_id => $admin) {
            $info[$parent_id] = AdminMgr::_createFromApi($this)->getParentVLimitAllRoomUsed($parent_id);
        }
        $over = [];
        foreach ($info as $parent_id => $item) {
            list($vlimit_all_room, $vlimit_used) = [$item['vlimit_all_room'], $item['vlimit_used']];
            if ($vlimit_used > $vlimit_all_room) {
                $item['name'] = AdminUser::name($parent_id);
                $item['note'] = AdminUser::admin_note($parent_id);
                $over[$parent_id] = $item;
            }
        }
        return [
            'over' => $over,
            'info' => $info
        ];
    }

    /**
     * 批量处理 菜单权限 目标  代理客户
     * @param string $admin_slug
     * @return array
     */
    public function allVLimitOnlineNumUsedForAgentBySlug($admin_slug = AdminUser::SLUG_AGENT)
    {
        $adminDict = AdminUser::dictItem([
            'admin_slug' => Q::where($admin_slug, '=', function () use ($admin_slug) {
                return !empty($admin_slug);
            }),
            'state' => [StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE],
            'admin_type' => AdminTypeEnum::AGENT_VALUE,
        ]);
        $info = [];
        foreach ($adminDict as $agent_id => $admin) {
            $info[$agent_id] = AdminMgr::_createFromApi($this)->getAgentVLimitOnlineNumUsed($agent_id);
        }

        $over = [];
        foreach ($info as $agent_id => $item) {
            list($vlimit_online_num, $vlimit_used) = [$item['vlimit_online_num'], $item['vlimit_used']];
            if ($vlimit_used > $vlimit_online_num) {
                $item['name'] = AdminUser::name($agent_id);
                $item['note'] = AdminUser::admin_note($agent_id);
                $over[$agent_id] = $item;
            }
        }
        return [
            'over' => $over,
            'info' => $info
        ];
    }

    /**
     * 批量处理 菜单权限 目标  自营客户
     * @param $account_credit
     * @param string $admin_slug
     * @return array
     */
    public function fixAccountCreditForParentBySlug($account_credit, $admin_slug = AdminUser::SLUG_PARENT)
    {
        $adminDict = AdminUser::dictItem([
            'admin_slug' => Q::where($admin_slug, '=', function () use ($admin_slug) {
                return !empty($admin_slug);
            }),
            'state' => [StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE],
            'admin_type' => AdminTypeEnum::PARENT_VALUE,
        ]);
        $info = [];
        foreach ($adminDict as $parent_id => $admin) {
            $_account_credit = AdminUser::account_credit($parent_id);
            if ($_account_credit <= 0) {
                $_account_credit = $account_credit;
                AdminUser::setOneById($parent_id, [
                    'account_credit' => $account_credit
                ]);
            }
            $info[$parent_id] = $_account_credit;
        }
        return [
            'info' => $info
        ];
    }

    /**
     * 批量处理 菜单权限 目标  代理客户
     * @param $account_credit
     * @param string $admin_slug
     * @return array
     */
    public function fixAccountCreditForAgentBySlug($account_credit, $admin_slug = AdminUser::SLUG_AGENT)
    {
        $adminDict = AdminUser::dictItem([
            'admin_slug' => Q::where($admin_slug, '=', function () use ($admin_slug) {
                return !empty($admin_slug);
            }),
            'state' => [StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE],
            'admin_type' => AdminTypeEnum::AGENT_VALUE,
        ]);
        $info = [];
        foreach ($adminDict as $agent_id => $admin) {
            $_account_credit = AdminUser::account_credit($agent_id);
            if ($_account_credit <= 0) {
                $_account_credit = $account_credit;
                AdminUser::setOneById($agent_id, [
                    'account_credit' => $account_credit
                ]);
            }
            $info[$agent_id] = $_account_credit;
        }

        return [
            'info' => $info
        ];
    }


    ################################################################
    ###########################  修复设置 ##########################
    ################################################################

    public function fixRoomRunningByDms($room_id, $timestamp = 0){
        $timestamp = !empty($timestamp) && $timestamp > 0 ? intval($timestamp) : time();
        $info = RoomRecord::_fetchRoomRunningByDms($room_id, $timestamp);

        return [
            'info' => $info,
            'room_id' => $room_id,
            'timestamp' => $timestamp,
        ];
    }

    public function fixReFetchLiveState($app, $stream)
    {
        if (empty($app) || empty($stream)) {
            throw new ApiParamsError('参数错误');
        }
        $stream_id = StreamBase::checkItem([
            'mcs_app' => $app,
            'mcs_stream' => Q::where("{$stream}%", 'like'),
        ]);
        if (empty($stream_id)) {
            throw new ApiParamsError('无对应视频流');
        }
        $stream = StreamBase::getOneById($stream_id);

        $api = Util::wsOpenApi();
        $info = [];
        $infoRst = $api->liveInfo($stream['mcs_app'], $stream['mcs_stream']);
        if (!empty($infoRst['code']) && $infoRst['code'] == 100) {
            $live_state = !empty($infoRst['data']['living']) ? LiveStateEnum::START_VALUE : LiveStateEnum::OVER_VALUE;
            $last_live_state = StreamBase::live_state($stream_id);
            if ($last_live_state != $live_state) {
                StreamBase::setStreamBaseLiveState($stream_id, $live_state);
                $info["{$stream['mcs_app']}/{$stream['mcs_stream']}"] = [
                    'live_state' => $live_state,
                    'last_live_state' => $last_live_state
                ];
            }
        }
        return [
            'info' => $info,
            'infoRst' => $infoRst,
            'stream_id' => $stream_id,
        ];
    }

    /**
     * 替换 MCS 帐号 配置 视频流 vhost
     * @param array $mcsDict MCS 数据列表
     * @param string $old_host 旧的 视频流 域名
     * @param string $new_host 新的 视频流 域名
     * @return array
     * @throws \app\Exception\ConfigError
     */
    private static function _fixMcsHost(array $mcsDict, $old_host = '', $new_host = '')
    {
        $info = [];
        foreach ($mcsDict as $stream_id => $mcs) {
            $mcs_id = $mcs->mcs_id;
            $mcsRst = Util::wsOpenApi()->infoMcs($mcs_id, false);
            if (empty($mcsRst['code']) || $mcsRst['code'] != 100) {
                continue;
            }
            $fix = [
                'play_rtmp_url' => str_replace($old_host, $new_host, $mcsRst['data']['play_rtmp_url']),
                'play_hls_url' => str_replace($old_host, $new_host, $mcsRst['data']['play_hls_url']),
                'stream' => str_replace($old_host, $new_host, $mcsRst['data']['stream']),
            ];
            if (!empty($old_host) && !empty($new_host)) {
                Util::wsOpenApi()->editMcs($mcs_id, $fix);
            }
            $info[$mcs_id] = $fix;
        }

        return $info;
    }

    /**
     * 批量修复 客户下属 MCS 帐号 视频流 地址
     * @param int $parent_id
     * @param string $old_host 旧的 视频流 域名
     * @param string $new_host 新的 视频流 域名
     * @return array
     * @throws \app\Exception\ConfigError
     */
    public function fixMcsHostByParentId($parent_id, $old_host = '', $new_host = '')
    {
        $stream_id_set = array_keys(StreamBase::dictItem([
            'admin_id' => Q::where($parent_id)
        ]));
        $mcsDict = StreamMcs::dictItem([
            'stream_id' => Q::whereIn($stream_id_set)
        ]);
        $info = self::_fixMcsHost($mcsDict, $old_host, $new_host);

        return [
            'info' => $info
        ];
    }

    /**
     * 批量修复 代理下属 MCS 帐号 视频流 地址
     * @param int $agent_id
     * @param string $old_host 旧的 视频流 域名
     * @param string $new_host 新的 视频流 域名
     * @return array
     * @throws \app\Exception\ConfigError
     */
    public function fixMcsHostByAgentId($agent_id, $old_host = '', $new_host = '')
    {
        $parent_id_set = array_keys(AdminUser::dictItem([
            'agent_id' => $agent_id
        ]));
        $stream_id_set = array_keys(StreamBase::dictItem([
            'admin_id' => Q::whereIn($parent_id_set)
        ]));
        $mcsDict = StreamMcs::dictItem([
            'stream_id' => Q::whereIn($stream_id_set)
        ]);
        $info = self::_fixMcsHost($mcsDict, $old_host, $new_host);

        return [
            'info' => $info
        ];
    }

    /**
     * 批量处理 菜单权限 目标  自营客户
     * @param string $admin_slug
     * @param int $state
     * @param string $pre_fix
     * @return array
     */
    public function aclMenuForParentBySlug($admin_slug = AdminUser::SLUG_PARENT, $state = StateEnum::NORMAL_VALUE, $pre_fix = '')
    {
        $adminDict = AdminUser::dictItem([
            'admin_slug' => Q::where($admin_slug, '=', function () use ($admin_slug) {
                return !empty($admin_slug);
            }),
            'admin_type' => AdminTypeEnum::PARENT_VALUE,
        ]);
        $info = [];
        foreach ($adminDict as $admin_id => $admin) {
            $info[$admin_id] = self::_setAllMenuACLByAdminId(AdminTypeEnum::PARENT_VALUE, $admin_id, $state, $pre_fix);
        }

        return [
            'info' => $info
        ];
    }

    /**
     * 批量处理 菜单权限 目标  代理客户
     * @param string $admin_slug
     * @param int $state
     * @param string $pre_fix
     * @return array
     */
    public function aclMenuForAgentBySlug($admin_slug = AdminUser::SLUG_AGENT, $state = StateEnum::NORMAL_VALUE, $pre_fix = '')
    {
        $adminDict = AdminUser::dictItem([
            'admin_slug' => Q::where($admin_slug, '=', function () use ($admin_slug) {
                return !empty($admin_slug);
            }),
            'admin_type' => AdminTypeEnum::AGENT_VALUE,
        ]);
        $info = [];
        foreach ($adminDict as $admin_id => $admin) {
            $info[$admin_id] = self::_setAllMenuACLByAdminId(AdminTypeEnum::AGENT_VALUE, $admin_id, $state, $pre_fix);
        }

        return [
            'info' => $info
        ];
    }

    /**
     * 构建 设置 数组
     * @param int $vlimit_per_parent
     * @param int $vlimit_per_room
     * @param int $vlimit_all_room
     * @param int $vlimit_online_num
     * @param int $nlimit_count_parent
     * @param int $nlimit_count_room
     * @param int $nlimit_count_sub
     * @param int $nlimit_count_player
     * @param int $nlimit_count_stream
     * @return array
     */
    private static function _buildLimitInfo($vlimit_per_parent, $vlimit_per_room, $vlimit_all_room, $vlimit_online_num, $nlimit_count_parent, $nlimit_count_room, $nlimit_count_sub, $nlimit_count_player, $nlimit_count_stream)
    {
        $args = self::_getMethodArgs(func_get_args(), __METHOD__);
        $ret = [];
        foreach ($args as $argk => $argv) {
            $argv = intval($argv);
            if ($argv >= 0) {
                $ret[$argk] = intval($argv);
            }
        }
        return $ret;
    }

    /**
     * 批量处理 修改 限制数据   目标  自营客户
     * @param string $admin_slug
     * @param int $vlimit_per_parent INTEGER  对下属客户 最大并发数限制  一般用于代理，0为无限制
     * @param int $vlimit_per_room INTEGER  对下属每个频道 最大并发数限制  一般用于代理，0为无限制
     * @param int $vlimit_all_room INTEGER  对下属所有频道 最大并发数限制 之和 一般用于客户，0为无限制
     * @param int $vlimit_online_num INTEGER  账号  最大并发数限制 一般用于客户 和 代理，0为无限制
     * @param int $nlimit_count_parent INTEGER  对下属 客户 总数限制  一般用于代理，0为无限制
     * @param int $nlimit_count_room INTEGER  对下属 频道 总数限制  一般用于客户，0为无限制
     * @param int $nlimit_count_sub INTEGER  对下属 子账号 总数限制 一般用于客户，0为无限制
     * @param int $nlimit_count_player INTEGER  对下属 播放器 总数限制 一般用于客户，0为无限制
     * @param int $nlimit_count_stream INTEGER  对下属 视频流 总数限制 一般用于客户，0为无限制
     * @return array
     */
    public function setLimitNumForParentBySlug($admin_slug = AdminUser::SLUG_PARENT, $vlimit_per_parent = -1, $vlimit_per_room = -1, $vlimit_all_room = -1, $vlimit_online_num = -1, $nlimit_count_parent = -1, $nlimit_count_room = -1, $nlimit_count_sub = -1, $nlimit_count_player = -1, $nlimit_count_stream = -1)
    {
        $adminDict = AdminUser::dictItem([
            'admin_slug' => Q::where($admin_slug, '=', function () use ($admin_slug) {
                return !empty($admin_slug);
            }),
            'admin_type' => AdminTypeEnum::PARENT_VALUE,
        ]);
        $update = self::_buildLimitInfo($vlimit_per_parent, $vlimit_per_room, $vlimit_all_room, $vlimit_online_num, $nlimit_count_parent, $nlimit_count_room, $nlimit_count_sub, $nlimit_count_player, $nlimit_count_stream);

        $info = [];
        foreach ($adminDict as $admin_id => $admin) {
            $info[$admin_id] = AdminUser::setOneById($admin_id, $update);
        }

        return [
            'info' => $info
        ];
    }

    /**
     * 批量处理 修改 限制数据   目标  代理客户
     * @param string $admin_slug
     * @param int $vlimit_per_parent INTEGER  对下属客户 最大并发数限制  一般用于代理，0为无限制
     * @param int $vlimit_per_room INTEGER  对下属每个频道 最大并发数限制  一般用于代理，0为无限制
     * @param int $vlimit_all_room INTEGER  对下属所有频道 最大并发数限制 之和 一般用于客户，0为无限制
     * @param int $vlimit_online_num INTEGER  账号  最大并发数限制 一般用于客户 和 代理，0为无限制
     * @param int $nlimit_count_parent INTEGER  对下属 客户 总数限制  一般用于代理，0为无限制
     * @param int $nlimit_count_room INTEGER  对下属 频道 总数限制  一般用于客户，0为无限制
     * @param int $nlimit_count_sub INTEGER  对下属 子账号 总数限制 一般用于客户，0为无限制
     * @param int $nlimit_count_player INTEGER  对下属 播放器 总数限制 一般用于客户，0为无限制
     * @param int $nlimit_count_stream INTEGER  对下属 视频流 总数限制 一般用于客户，0为无限制
     * @return array
     */
    public function setLimitNumForAgentBySlug($admin_slug = AdminUser::SLUG_AGENT, $vlimit_per_parent = -1, $vlimit_per_room = -1, $vlimit_all_room = -1, $vlimit_online_num = -1, $nlimit_count_parent = -1, $nlimit_count_room = -1, $nlimit_count_sub = -1, $nlimit_count_player = -1, $nlimit_count_stream = -1)
    {
        $adminDict = AdminUser::dictItem([
            'admin_slug' => Q::where($admin_slug, '=', function () use ($admin_slug) {
                return !empty($admin_slug);
            }),
            'admin_type' => AdminTypeEnum::AGENT_VALUE,
        ]);
        $update = self::_buildLimitInfo($vlimit_per_parent, $vlimit_per_room, $vlimit_all_room, $vlimit_online_num, $nlimit_count_parent, $nlimit_count_room, $nlimit_count_sub, $nlimit_count_player, $nlimit_count_stream);

        $info = [];
        foreach ($adminDict as $admin_id => $admin) {
            $info[$admin_id] = AdminUser::setOneById($admin_id, $update);
        }

        return [
            'info' => $info
        ];
    }

    public function setVLimitAllRoomAndVLimitAllRoomForParentBySlug($admin_slug = AdminUser::SLUG_PARENT)
    {
        $adminDict = AdminUser::dictItem([
            'admin_slug' => Q::where($admin_slug, '=', function () use ($admin_slug) {
                return !empty($admin_slug);
            }),
            'admin_type' => AdminTypeEnum::PARENT_VALUE,
        ]);
        $info = [];
        foreach ($adminDict as $admin_id => $admin) {
            $vlimit_all_room = AdminUser::vlimit_all_room($admin_id);
            $vlimit_online_num = AdminUser::vlimit_online_num($admin_id);
            $limit = $vlimit_all_room > $vlimit_online_num ? $vlimit_all_room : $vlimit_online_num;
            if ($limit <= 0) {
                continue;
            }
            $update = [];
            if ($limit != $vlimit_online_num) {
                $update['vlimit_online_num'] = $limit;
            }
            if ($limit != $vlimit_all_room) {
                $update['vlimit_all_room'] = $limit;
            }
            if (!empty($update)) {
                $info[$admin_id] = AdminUser::setOneById($admin_id, $update);
            }
        }

        return [
            'info' => $info
        ];
    }

    /**
     * 批量处理 修改硬性上限  修改为 套餐额度的倍数 目标  自营客户
     * @param int $ratio 倍率
     * @param int $min
     * @param string $admin_slug
     * @return array
     */
    public function setVLimitOnlineNumForParentBySlug($ratio = 2, $min = 20, $admin_slug = AdminUser::SLUG_PARENT)
    {
        $ratio = floatval($ratio);
        $min = intval($min);
        $adminDict = AdminUser::dictItem([
            'admin_slug' => $admin_slug,
            'admin_type' => AdminTypeEnum::PARENT_VALUE,
        ]);
        $info = [];
        foreach ($adminDict as $admin_id => $admin) {
            $package_num = XdyIsvMgr::_activeOnlineNumberLimit($admin_id);
            $package_num_to = intval($package_num * $ratio);
            $package_num_to = $package_num_to > $min ? $package_num_to : $min;
            $info[$admin_id] = $package_num_to;
            AdminUser::vlimit_online_num($admin_id) < $package_num_to && AdminUser::setOneById($admin_id, [
                'vlimit_online_num' => $package_num_to
            ]);
        }

        return [
            'info' => $info
        ];
    }

    /**
     * 批量处理 修改硬性上限  修改为 套餐额度的倍数 目标  代理客户
     * @param int $ratio
     * @param int $min
     * @param string $admin_slug
     * @return array
     */
    public function setVLimitOnlineNumForAgentBySlug($ratio = 2, $min = 20, $admin_slug = AdminUser::SLUG_AGENT)
    {
        $ratio = floatval($ratio);
        $min = intval($min);
        $adminDict = AdminUser::dictItem([
            'admin_slug' => $admin_slug,
            'admin_type' => AdminTypeEnum::AGENT_VALUE,
        ]);
        $info = [];
        foreach ($adminDict as $admin_id => $admin) {
            $package_num = XdyIsvMgr::_activeOnlineNumberLimit($admin_id);
            $package_num_to = intval($package_num * $ratio);
            $package_num_to = $package_num_to > $min ? $package_num_to : $min;
            $info[$admin_id] = $package_num_to;
            AdminUser::vlimit_online_num($admin_id) < $package_num_to && AdminUser::setOneById($admin_id, [
                'vlimit_online_num' => $package_num_to
            ]);
        }

        return [
            'info' => $info
        ];
    }

    /**
     * 批量处理 修改超出套餐价格 目标  自营客户
     * @param $over_price
     * @param string $admin_slug
     * @return array
     * @throws ApiParamsError
     */
    public function setOverPriceForParentBySlug($over_price, $admin_slug = AdminUser::SLUG_PARENT)
    {
        $over_price = floatval($over_price);
        if (empty($over_price)) {
            throw  new ApiParamsError('参数错误');
        }

        $adminDict = AdminUser::dictItem([
            'admin_slug' => $admin_slug,
            'admin_type' => AdminTypeEnum::PARENT_VALUE,
        ]);
        $info = [];
        foreach ($adminDict as $admin_id => $admin) {
            $info[$admin_id] = AdminUser::setOneById($admin_id, [
                'limit_onlinenumber_over_price' => $over_price
            ]);
        }

        return [
            'info' => $info
        ];
    }

    /**
     * 批量处理 修改超出套餐价格 目标  代理客户
     * @param $over_price
     * @param string $admin_slug
     * @return array
     * @throws ApiParamsError
     */
    public function setOverPriceForAgentBySlug($over_price, $admin_slug = AdminUser::SLUG_AGENT)
    {
        $over_price = floatval($over_price);
        if (empty($over_price)) {
            throw  new ApiParamsError('参数错误');
        }

        $adminDict = AdminUser::dictItem([
            'admin_slug' => $admin_slug,
            'admin_type' => AdminTypeEnum::AGENT_VALUE,
        ]);
        $info = [];
        foreach ($adminDict as $admin_id => $admin) {
            $info[$admin_id] = AdminUser::setOneById($admin_id, [
                'limit_onlinenumber_over_price' => $over_price
            ]);
        }

        return [
            'info' => $info
        ];
    }

    /**
     * 手动设置 某个 stream_mcs 数据表 $stream_id 为特定的 $mcs_id
     * @param int $stream_id
     * @param int $mcs_id
     * @return array
     * @throws \app\Exception\ConfigError
     */
    public function setStreamMcsId($stream_id, $mcs_id)
    {
        $tmpRst = Util::wsOpenApi()->infoMcs($mcs_id);
        $info = $tmpRst;
        if ($tmpRst['code'] == 100) {
            StreamMcs::upsertItem([
                'stream_id' => $stream_id
            ], [
                'mcs_id' => $mcs_id,
                'stream_id' => $stream_id,
                'mcs_config' => json_encode($tmpRst['data']),
            ]);
        }

        $rst = ['msg' => '设置成功', 'data' => $info];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    ################################################################
    ###########################  测试密码函数 ##########################
    ################################################################

    /**
     * 测试 Admin 管理员 密码
     * admin_user 数据表
     * @param int $admin_id
     * @return array
     * @throws \Tiny\Exception\OrmStartUpError
     */
    public function testAdminPwd($admin_id)
    {
        $admin = AdminUser::_first(AdminUser::tableBuilder([
            'admin_id' => $admin_id
        ]));
        $pwd = !empty($admin->pasw) ? $admin->pasw : '';
        return [
            'admin_id' => $admin_id,
            'pwd' => $pwd,
            'str' => AdminUser::_decode($pwd)
        ];
    }

    /**
     * 测试 Mgr 管理员 密码
     * site_mgr_user 数据表
     * @param int $mgr_id
     * @return array
     * @throws \Tiny\Exception\OrmStartUpError
     */
    public function testMgrPwd($mgr_id)
    {
        $mgr = SiteMgrUser::_first(SiteMgrUser::tableBuilder([
            'mgr_id' => $mgr_id
        ]));
        $pwd = !empty($mgr->pasw) ? $mgr->pasw : '';
        return [
            'mgr_id' => $mgr_id,
            'pwd' => $pwd,
            'str' => SiteMgrUser::_decode($pwd)
        ];
    }

    public function testUserInfo($agent_rand, $user_id)
    {
        $ipInfo = IpAddrHelper::loadClientIp($agent_rand, $user_id);
        return [
            'ipInfo' => $ipInfo,
        ];
    }

    public function testSendEmail($email = '930393117@qq.com', $subject = 'test subject', $content = 'test content')
    {
        return [
            'email' => $email,
            'subject' => $subject,
            'content' => $content,
            'ret' => Util::sendEmail($email, $subject, $content),
            'emailConfig' => App::config('services.email'),
        ];
    }


    public function testYunPianSms($phone_num, $msg = '【鑫斗云】欢迎进入鑫斗云，您的登陆验证码是1234')
    {
        return [
            'phone_num' => $phone_num,
            'msg' => $msg,
            'ret' => Util::yunPianSms($phone_num, $msg)
        ];
    }


    ################################################################
    ###########################  常用修复函数 ##########################
    ################################################################

    /**
     * 根据 日期 调用 loadAndFixRoomSubRunningByRange
     * 复制 room_running_dms 数据 ref_host = '' room_id > 0 范围内条目 到 room_running
     * 并执行 fixAllRoomSubRunningByRange 修复 room_running 系列 表 刻度为 5minute hour day 的数据
     * @param int $per_day 日期 格式为 20170101
     * @return array
     * @throws ApiParamsError
     * @throws \Tiny\Exception\OrmStartUpError
     */
    public function fixRoomSubRunningByDay($per_day)
    {
        $per_day = intval($per_day);
        if ($per_day <= Util::MIN_DATE_PER_DAY) {
            throw new ApiParamsError("args error per_day:{$per_day}");
        }

        list($time_s, $time_e) = [strtotime("{$per_day} 00:00:00"), strtotime("{$per_day} 23:59:59")];
        $time_e = $time_e > time() ? time() : $time_e;
        return $this->loadAndFixRoomSubRunningByRange($time_s, $time_e);
    }

    /**
     * 复制 room_running_dms 数据 ref_host = '' room_id > 0 范围内条目 到 room_running
     * 并执行 fixAllRoomSubRunningByRange 修复 room_running 系列 表 刻度为 5minute hour day 的数据
     * @param int $time_s 时间戳
     * @param int $time_e 时间戳
     * @return array
     * @throws ApiParamsError
     * @throws \Tiny\Exception\OrmStartUpError
     */
    public function loadAndFixRoomSubRunningByRange($time_s, $time_e)
    {
        return [
            'load' => self::_loadDmsRunningAndFixByRange($time_s, $time_e),
            'fix' => $this->fixAllRoomSubRunningByRange($time_s, $time_e)
        ];
    }

    /**
     * 复制 room_running_dms 数据 ref_host = '' room_id > 0 范围内条目 到 room_running
     * @param int $time_s 时间戳
     * @param int $time_e 时间戳
     * @return array
     */
    private static function _loadDmsRunningAndFixByRange($time_s, $time_e)
    {
        $type = 'minute';
        list($timer_s, $timer_e) = [intval($time_s / 60), intval($time_e / 60)];
        $dmsList = RoomRunningDms::dictItem([
            'timer_type' => $type,
            'timer_count' => Q::whereBetween($timer_s, $timer_e),
            'ref_host' => '',
            'room_id' => Q::where(0, '>'),
        ]);
        $info = [];
        foreach ($dmsList as $item) {
            $where = [
                'room_id' => $item->room_id,
                'admin_id' => $item->admin_id,
                'timer_type' => $item->timer_type,
                'timer_count' => $item->timer_count,
                'ref_host' => '',
            ];
            $tmp = RoomRunning::firstItem($where);
            if (empty($tmp)) {
                $id = RoomRunning::createOne([
                    'room_id' => $item->room_id,
                    'admin_id' => $item->admin_id,
                    'timer_type' => $item->timer_type,
                    'timer_count' => $item->timer_count,
                    'created_at' => date('Y-m-d H:i:s', $item->timer_count * 60),
                    'num' => $item->num,
                    'ref_host' => '',
                    'live_state' => $item->live_state,
                ]);
                $info[] = $id;
            }
        }
        return $info;
    }


    ################################################################
    ###########################  数据初始化 ##########################
    ################################################################

    public static function _dbFillRoomMaxViewerByDaily($useDms)
    {
        $allRoom = LiveRoom::dictItem();
        $info = [];
        foreach ($allRoom as $room_id => $item) {
            $where = [
                'room_id' => $room_id,
            ];
            $maxItem = $useDms ? DailyRoomRunningDms::firstItem($where, ['num_max', 'desc']) : DailyRoomRunning::firstItem($where, ['num_max', 'desc']);
            if (empty($maxItem)) {
                continue;
            }
            $info[$room_id] = LiveRoom::setOneById($room_id, [
                'viewer_max' => $maxItem->num_max,
                'viewer_max_at' => $maxItem->num_max_time
            ]);
        }
        return $info;
    }

    public function dbFillRoomMaxViewerByDaily($useDms = 1)
    {
        $info = self::_dbFillRoomMaxViewerByDaily($useDms);
        return [
            'info' => $info
        ];
    }

    public static function _dbFillParentMaxViewerByDaily($useDms)
    {
        $allParent = AdminUser::dictItem([
            'admin_type' => AdminTypeEnum::PARENT_VALUE
        ]);
        $info = [];
        foreach ($allParent as $admin_id => $parent) {
            $where = [
                'room_id' => 0,
                'admin_id' => $admin_id,
            ];
            $maxItem = $useDms ? DailyRoomRunningDms::firstItem($where, ['num_max', 'desc']) : DailyRoomRunning::firstItem($where, ['num_max', 'desc']);
            if (empty($maxItem)) {
                continue;
            }
            $info[$admin_id] = AdminUser::setOneById($admin_id, [
                'viewer_max' => $maxItem->num_max,
                'viewer_max_at' => $maxItem->num_max_time
            ]);
        }
        return $info;
    }

    public function dbFillParentMaxViewerByDaily($useDms = 1)
    {
        $info = self::_dbFillParentMaxViewerByDaily($useDms);
        return [
            'info' => $info
        ];
    }

    public function dbClearRoomAndAdminMaxViewer($max_day = 32)
    {
        $ret1 = LiveRoom::_update(LiveRoom::tableBuilderEx([
            'viewer_max' => Q::where(0, '>'),
            'viewer_max_at' => Q::where(date('Y-m-d H:i:s', time() - $max_day * 24 * 3600), '>')
        ]), [
            'viewer_max' => 0,
            'viewer_max_at' => '0000-00-00 00:00:00'
        ]);
        $ret2 = AdminUser::_update(AdminUser::tableBuilderEx([
            'viewer_max' => Q::where(0, '>'),
            'viewer_max_at' => Q::where(date('Y-m-d H:i:s', time() - $max_day * 24 * 3600), '>')
        ]), [
            'viewer_max' => 0,
            'viewer_max_at' => '0000-00-00 00:00:00'
        ]);
        return [
            'ret1' => $ret1,
            'ret2' => $ret2,
        ];
    }

    public function dbFillAllAdminAndMgrPwd($pwd = 'ws123456')
    {
        if (!App::config('app.dev_fill_pwd') || !App::dev()) {
            throw new ApiParamsError("no dev or app.dev_fill_pwd");
        }
        $adminArr = [];
        $allAdmin = AdminUser::dictItem();
        foreach ($allAdmin as $admin_id => $admin) {
            $adminArr[] = $admin_id;
            AdminUser::setItem($admin_id, [
                'pasw' => $pwd
            ], false);
        }
        $mgrArr = [];
        $addMgr = SiteMgrUser::dictItem();
        foreach ($addMgr as $mgr_id => $mgr) {
            $mgrArr[] = $mgr_id;
            SiteMgrUser::setItem($mgr_id, [
                'pasw' => $pwd
            ], false);
        }
        return [
            'adminArr' => $adminArr,
            'mgrArr' => $mgrArr,
        ];
    }

    public function dbFillLiveRoomAgentIdByAdminId()
    {
        $dict = LiveRoom::dictItem([
            'agent_id' => 0,
        ]);
        $info = [];
        foreach ($dict as $room_id => $item) {
            $admin_id = Util::v($item, 'admin_id', 0);
            $agent_id = AdminUser::agent_id($admin_id);
            if ($agent_id > 0) {
                LiveRoom::_update(LiveRoom::tableBuilderEx([
                    'room_id' => $room_id
                ]), [
                    'agent_id' => $agent_id,
                ]);
                $info[$room_id] = $agent_id;
            }
        }
        return [
            'info' => $info
        ];
    }

    public function dbFillPlayerBaseAgentIdByAdminId()
    {
        $dict = PlayerBase::dictItem([
            'agent_id' => 0,
        ]);
        $info = [];
        foreach ($dict as $player_id => $item) {
            $admin_id = Util::v($item, 'admin_id', 0);
            $agent_id = AdminUser::agent_id($admin_id);
            if ($agent_id > 0) {
                PlayerBase::_update(PlayerBase::tableBuilderEx([
                    'player_id' => $player_id
                ]), [
                    'agent_id' => $agent_id,
                ]);
                $info[$player_id] = $agent_id;
            }
        }
        return [
            'info' => $info
        ];
    }

    public function dbFillStreamBaseAgentIdByAdminId()
    {
        $dict = StreamBase::dictItem([
            'agent_id' => 0,
        ]);
        $info = [];
        foreach ($dict as $stream_id => $item) {
            $admin_id = Util::v($item, 'admin_id', 0);
            $agent_id = AdminUser::agent_id($admin_id);
            if ($agent_id > 0) {
                StreamBase::_update(StreamBase::tableBuilderEx([
                    'stream_id' => $stream_id
                ]), [
                    'agent_id' => $agent_id,
                ]);
                $info[$stream_id] = $agent_id;
            }
        }
        return [
            'info' => $info
        ];
    }

    /**
     * @return array
     */
    public function dbFillDailyViewCount()
    {
        $info = [];
        $sql = [];
        foreach (['total_ip', 'total_view_record', 'total_unique_user', 'total_interval_time'] as $key) {
            $tmp_sql = <<<EOT
UPDATE `daily_view_count` SET `dms_{$key}` = `{$key}` WHERE `dms_{$key}` = 0 AND `{$key}` > 0
EOT;
            $sql[$key] = $tmp_sql;
            $info[$key] = DbHelper::initDb()->getConnection()->update($tmp_sql);
        }
        return ['msg' => "修复成功", 'sql' => $sql, 'data' => $info];
    }


    /**
     * 修改数据表 自增id
     * @param $table  string  数据表名
     * @param $increment  int  自增id
     * @return array
     * @throws ApiParamsError
     */
    public function dbSetTableAutoIncrement($table, $increment)
    {
        $increment = intval($increment);
        $allow_tables = Util::build_map([
            'xdy_order', 'admin_user', 'stream_mcs', 'stream_base', 'live_room', 'admin_countly', 'xdy_admin_product', 'xdy_product', 'article_list', 'room_content_config', 'site_mgr_user', 'article_classify', 'table_op_record', 'stream_vod', 'stream_pull', 'room_publish_record', 'player_mps', 'player_base', 'player_aodian', 'player_ali',
            'room_view_record_dms', 'room_view_record'
        ]);
        if (empty($allow_tables[$table]) || $increment <= 0) {
            throw new ApiParamsError("参数错误 table:{$table}");
        }
        $cmd = 'ALTER TABLE';
        $sql = "{$cmd} {$table} AUTO_INCREMENT={$increment}";
        $info = DbHelper::initDb()->getConnection()->statement($sql);
        return ['msg' => "修复成功", 'sql' => "sql:{$sql}", 'data' => $info];
    }


    #############################################################
    ####################  修复 room running ######################
    #############################################################

    /**
     * 修复数据 指定运行 某一类 定时任务
     * 对应  room_running          crontabRoomMinuteRunning
     * 对应  room_running_dms      crontabRoomMinuteRunning
     * 对应  room_running_dms_ref  crontabHostMinuteRunning
     * 对应  room_running_dms_sum  crontabSumAdminMinuteRunning
     * @param int $timer_s
     * @param int $timer_e
     * @param int $size
     * @param string $timer_type
     * @param string $target
     * @return array
     * @throws ApiParamsError
     * @throws \Tiny\Exception\OrmStartUpError
     */
    private static function _finAllRoomMinuteRunning($timer_s, $timer_e, $size, $timer_type, $target = 'room_running')
    {
        $timer_s = intval($timer_s);
        $timer_e = intval($timer_e);
        if ($timer_s <= 0 || $timer_e <= 0 || $timer_e <= $timer_s) {
            throw new ApiParamsError("args error timer_e:{$timer_e}, timer_s:{$timer_s}");
        }

        $rst = [];
        for ($idx = $timer_s; $idx <= $timer_e + $size; $idx += $size) {
            if ($target == 'room_running') {
                $rst[] = RoomRecord::crontabRoomMinuteRunning($idx * 60, $size, $timer_type);
            } elseif ($target == 'room_running_dms') {
                $rst[] = RoomRecord::crontabRoomMinuteRunning($idx * 60, $size, $timer_type, 1);
            } elseif ($target == 'room_running_dms_ref') {
                $rst[] = RoomRecord::crontabHostMinuteRunning($idx * 60, $size, $timer_type);
            } elseif ($target == 'room_running_dms_sum') {
                $rst[] = RoomRecord::crontabSumHostAdminMinuteRunning($idx * 60, $size, $timer_type);
            } elseif ($target == 'room_running_sum') {
                $rst[] = RoomRecord::crontabSumAdminMinuteRunning($idx * 60, $size, $timer_type);
            }
        }
        return $rst;
    }

    /**
     * 修复 room_running 系列 表 刻度为 5minute hour day 的数据
     * @param int $per_day 日期 格式为 20170101
     * @param string $target
     * @return array
     * @throws ApiParamsError
     * @throws \Tiny\Exception\OrmStartUpError
     */
    public function fixAllRoomSubRunningByDay($per_day, $target = 'room_running')
    {
        $per_day = intval($per_day);
        if ($per_day <= Util::MIN_DATE_PER_DAY) {
            throw new ApiParamsError("args error per_day:{$per_day}");
        }

        list($time_s, $time_e) = [strtotime("{$per_day} 00:00:00"), strtotime("{$per_day} 23:59:59")];
        $time_e = $time_e > time() ? time() : $time_e;
        return $this->fixAllRoomSubRunningByRange($time_s, $time_e, $target);
    }

    /**
     * 修复 room_running 系列 表 刻度为 5minute hour day 的数据
     * @param int $time_s 时间戳
     * @param int $time_e 时间戳
     * @param string $target
     * @return array
     * @throws ApiParamsError
     * @throws \Tiny\Exception\OrmStartUpError
     */
    public function fixAllRoomSubRunningByRange($time_s, $time_e, $target = 'room_running')
    {
        $time_s = intval($time_s);
        $time_e = intval($time_e);
        if ($time_s <= 0 || $time_e <= 0 || $time_e <= $time_s) {
            throw new ApiParamsError("args error, time_s:{$time_s}, time_e:{$time_e}");
        }
        if ($time_e - $time_s > 3600 * 24) {
            throw new ApiParamsError("time_s - time_e must lte 24 * 3600, time_s:{$time_s}, time_e:{$time_e}");
        }

        list($timer_s, $timer_e) = [intval($time_s / 60), intval($time_e / 60)];
        return [
            '5minute' => self::_finAllRoomMinuteRunning($timer_s, $timer_e, 5, '5minute', $target),
            'hour' => self::_finAllRoomMinuteRunning($timer_s, $timer_e, 60, 'hour', $target),
            'day' => self::_finAllRoomMinuteRunning($timer_s, $timer_e, 24 * 60, 'day', $target),
        ];
    }

    /**
     * 修复 room_running 系列 表 刻度为 5minute 的数据
     * @param int $timer_s 时间戳 整除 60
     * @param int $timer_e 时间戳 整除 60
     * @param string $target
     * @return array
     * @throws ApiParamsError
     * @throws \Tiny\Exception\OrmStartUpError
     */
    public function fixAllRoom5MinuteRunning($timer_s, $timer_e, $target = 'room_running')
    {
        return self::_finAllRoomMinuteRunning($timer_s, $timer_e, 5, '5minute', $target);
    }

    /**
     * 修复 room_running 系列 表 刻度为 hour 的数据
     * @param int $timer_s 时间戳 整除 60
     * @param int $timer_e 时间戳 整除 60
     * @param string $target
     * @return array
     * @throws ApiParamsError
     * @throws \Tiny\Exception\OrmStartUpError
     */
    public function fixAllRoomHourRunning($timer_s, $timer_e, $target = 'room_running')
    {
        return self::_finAllRoomMinuteRunning($timer_s, $timer_e, 60, 'hour', $target);
    }

    /**
     * 修复 room_running 系列 表 刻度为 day 的数据
     * @param int $timer_s 时间戳 整除 60
     * @param int $timer_e 时间戳 整除 60
     * @param string $target
     * @return array
     * @throws ApiParamsError
     * @throws \Tiny\Exception\OrmStartUpError
     */
    public function fixAllRoomDayRunning($timer_s, $timer_e, $target = 'room_running')
    {
        return self::_finAllRoomMinuteRunning($timer_s, $timer_e, 24 * 60, 'day', $target);
    }

    #############################################################
    ################  修复 用户 会话相关 ###################
    #############################################################

    /**
     * 根据 角色 id 查询已登录的用户信息
     * @param string $admin_type 角色 id
     * @return array session 中登录信息
     */
    public function ssGetUserLoginByType($admin_type = AdminTypeEnum::SUPER_VALUE)
    {
        return AdminAuth::getUserLoginMapByType($admin_type);
    }

    /**
     * 根据 用户 id 查询已登录的用户信息
     * @param int $admin_id 用户 id
     * @return array session 中登录信息
     */
    public function ssGetUserLoginByUid($admin_id)
    {
        return AdminAuth::getUserLoginMapByUid($admin_id);
    }


    /**
     * 根据 角色 id 删除 已登录的用户信息
     * @param string $admin_type 角色 id
     * @return array session 中登录信息
     * @throws \phpFastCache\Exceptions\phpFastCacheDriverCheckException
     */
    public function ssDelUserLoginByType($admin_type = AdminTypeEnum::SUPER_VALUE)
    {
        $list = AdminAuth::getUserLoginMapByType($admin_type);
        return self::_delUserLoginByMap($list);
    }

    /**
     * @param $list
     * @return array
     * @throws \phpFastCache\Exceptions\phpFastCacheDriverCheckException
     */
    private static function _delUserLoginByMap($list)
    {
        $ret = [];
        $redis = self::_getRedisInstance();
        $preKey = Boot::_getSessionPreKey();

        foreach ($list as $item) {
            $session_id = Util::v($item, 'session_id');
            $admin_id = Util::v($item, 'admin_id', 0);

            if (!empty($session_id)) {
                $sKey = trim("{$preKey}:{$session_id}");
                $redis->set($sKey, '');
                $redis->delete($sKey);

                if (!empty($admin_id)) {
                    AdminAuth::delUserTypeLoginMap($admin_id, $session_id);
                }
                $ret[] = $item;
            }

        }
        return $ret;
    }

    /**
     * 根据 用户 id 删除 已登录的用户信息
     * @param int $admin_id 用户 id
     * @return array session 中登录信息
     * @throws \phpFastCache\Exceptions\phpFastCacheDriverCheckException
     */
    public function ssDelUserLoginByUid($admin_id)
    {
        $list = AdminAuth::getUserLoginMapByUid($admin_id);
        return self::_delUserLoginByMap($list);
    }

    #############################################################
    ################  修复 room Daily running ###################
    #############################################################

    /**
     * 修复 按月份 room 每日峰值数据
     * 目标 daily_room_running  daily_room_running_dms  数据表  room_id > 0
     * @param  int $year 年
     * @param int $month 月
     * @return array
     * @throws ApiParamsError
     */
    public function fixAllRoomDailyRunning($year, $month)
    {
        list($year, $month) = [intval($year), intval($month)];
        $max_day = Util::max_days($year, $month);
        if ($year <= 0 || $month <= 0 || $max_day <= 0) {
            throw new ApiParamsError("args error year:{$year}, month:{$month}");
        }

        $use_dms = App::config('services.countly.use_dms', false);
        $rst = [];
        for ($idx = 1; $idx <= $max_day; $idx++) {
            $_day = $idx < 10 ? "0{$idx}" : "{$idx}";
            $_month = $month < 10 ? "0{$month}" : "{$month}";
            $per_day = intval("{$year}{$_month}{$_day}");
            if ($per_day >= intval(date('Ymd'))) {
                break;
            }
            $rst[] = RoomRecord::crontabRoomDailyRunning($per_day);
            if ($use_dms) {
                $rst[] = RoomRecord::crontabRoomDailyRunning($per_day, 1);
            }
        }
        return $rst;
    }

    /**
     * 修复 按月份 admin 每日峰值数据
     * 目标 daily_room_running  daily_room_running_dms  数据表  room_id = 0
     * @param  int $year
     * @param int $month
     * @return array
     * @throws ApiParamsError
     */
    public function fixAllAdminDailyRunning($year, $month)
    {
        list($year, $month) = [intval($year), intval($month)];
        $max_day = Util::max_days($year, $month);
        if ($year <= 0 || $month <= 0 || $max_day <= 0) {
            throw new ApiParamsError("args error year:{$year}, month:{$month}");
        }

        $use_dms = App::config('services.countly.use_dms', false);
        $rst = [];
        for ($idx = 1; $idx <= $max_day; $idx++) {
            $_day = $idx < 10 ? "0{$idx}" : "{$idx}";
            $_month = $month < 10 ? "0{$month}" : "{$month}";
            $per_day = intval("{$year}{$_month}{$_day}");
            if ($per_day >= intval(date('Ymd'))) {
                break;
            }
            $rst[] = RoomRecord::crontabAdminDailyRunning($per_day);
            if ($use_dms) {
                $rst[] = RoomRecord::crontabAdminDailyRunning($per_day, 1);
            }
        }
        return $rst;
    }

    public function fixMeaulPath()
    {
        //修复代理之前的播放器鉴权菜单
        $info[] = AdminAccessControl::_update(AdminAccessControl::tableBuilder([
            'access_value' => 'agent.agentcfg.player-acl',
            'state' => StateEnum::NORMAL_VALUE,
        ]), [
            'access_value' => 'agent.player.player-acl'
        ]);

        //修复客户之前的播放器鉴权菜单
        $info[] = AdminAccessControl::_update(AdminAccessControl::tableBuilder([
            'access_value' => 'parent.parentcfg.player-acl',
            'state' => StateEnum::NORMAL_VALUE,
        ]), [
            'access_value' => 'parent.player.player-acl'
        ]);

        $where = [
            'access_value' => 'agent.player.player-acl',
            'state' => StateEnum::NORMAL_VALUE,
        ];

        //修复主菜单
        $table = AdminAccessControl::tableBuilder($where);
        $list = AdminAccessControl::_get($table);

        foreach ($list as $item) {
            $admin_id = $item['admin_id'];
            $where = [
                'admin_id' => $admin_id,
                'access_value' => 'agent.player',
            ];

            $info = AdminAccessControl::_first(AdminAccessControl::tableBuilder($where));
            if (empty($info)) {
                $data = [
                    'admin_id' => $admin_id,
                    'access_type' => 'menu',
                    'access_value' => 'agent.player',
                    'state' => StateEnum::NORMAL_VALUE,
                ];
                $info[] = AdminAccessControl::createOne($data);
            }
        }

        $where = [
            'access_value' => 'parent.player.player-acl',
            'state' => StateEnum::NORMAL_VALUE,
        ];

        $table = AdminAccessControl::tableBuilder($where);
        $list = AdminAccessControl::_get($table);

        foreach ($list as $item) {
            $admin_id = $item['admin_id'];
            $where = [
                'admin_id' => $admin_id,
                'access_value' => 'parent.player',
            ];

            $info = AdminAccessControl::_first(AdminAccessControl::tableBuilder($where));
            if (empty($info)) {
                $data = [
                    'admin_id' => $admin_id,
                    'access_type' => 'menu',
                    'access_value' => 'parent.player',
                    'state' => StateEnum::NORMAL_VALUE,
                ];
                $info[] = AdminAccessControl::createOne($data);
            }
        }

        return $info;
    }

}