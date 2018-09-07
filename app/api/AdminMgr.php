<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/10/25
 * Time: 15:46
 */

namespace app\api;


use app\AdminController;
use app\api\Abstracts\AdminApi;
use app\api\GraphQL\AdminUser as GQLAdminUser;
use app\api\GraphQL_\Enum\AdminTypeEnum;
use app\api\GraphQL_\Enum\StateEnum;
use app\Exception\ApiAuthBeyondError;
use app\Exception\ApiParamsError;
use app\Model\AdminAccessControl;
use app\Model\AdminRecord;
use app\Model\AdminUser;
use app\Model\LiveRoom;
use app\Util;


class AdminMgr extends AdminApi
{
    protected static $_skip_vlimit_online_num_agent_list = [];

    public static function crontabCheckAdminExpirationDate($timestamp)
    {
        $adminDict = AdminUser::dictItem([
            'state' => StateEnum::NORMAL_VALUE,
            'admin_type' => AdminTypeEnum::PARENT_VALUE
        ]);
        $date_str = date('Y-m-d H:i:s', $timestamp);
        $info = [];
        foreach ($adminDict as $parent_id => $admin) {
            $expiration_date = !empty($admin['expiration_date']) ? $admin['expiration_date'] : '';
            $expiration_date = Util::trimlower($expiration_date);
            if (empty($expiration_date) || $expiration_date == '0000-00-00 00:00:00') {
                continue;
            }
            $expiration_date = date('Y-m-d H:i:s', strtotime($expiration_date));
            if ($expiration_date < $date_str) {
                $info[$parent_id] = $expiration_date;
                self::_stateAdminBySuper($parent_id, StateEnum::FROZEN_VALUE);
            }
        }
        return ['code' => 0, 'info' => $info];
    }

    public static function _stateAdminBySuper($admin_id, $state)
    {
        $api = self::createApiAsSuper();
        return $api->stateAdmin($admin_id, $state);
    }

    /**
     * 修改 admin 状态
     * @param int $admin_id admin ID
     * @param  int $state 参见 StateEnum   ['FROZEN' => 7, 'UNKNOWN' => 0, 'DELETED' => 9, 'NORMAL' => 1]
     * @return array ['msg' => '修改成功'];
     * @throws ApiParamsError
     */
    public function stateAdmin($admin_id, $state)
    {
        $state = intval($state);
        $admin_id = intval($admin_id);

        if (empty($state) || !in_array($state, array_values(StateEnum::ALL_ENUM_MAP)) || $state == StateEnum::NOTDEL_VALUE) {
            throw new ApiParamsError("参数错误 state:{$state}");
        }

        self::_checkBeyondAdmin($this->admin_id, $admin_id, "权限错误 cur_admin_id:{$this->admin_id}, admin_id:{$admin_id}", false);

        if (AdminUser::state($admin_id) == $state || AdminUser::state($admin_id) == StateEnum::DELETED_VALUE) {
            // 全部相同  或者已被删除的条目 跳过修改
        } else {

            $ip = $this->client_ip();
            $op_desc = '修改用户状态';
            if ($state == StateEnum::FROZEN_VALUE) {
                $op_desc = '冻结用户';
            } elseif ($state == StateEnum::DELETED_VALUE) {
                $op_desc = '删除用户';
            } elseif ($state == StateEnum::NORMAL_VALUE) {
                $expiration_date = AdminUser::expiration_date($admin_id);
                if (!empty($expiration_date) && $expiration_date != '0000-00-00 00:00:00') {
                    $expiration_date_int = strtotime($expiration_date);
                    if ($expiration_date_int <= time()) {
                        throw new ApiParamsError("请先延长该用户的有效期，然后进行操作");
                    }
                }
                $op_desc = '恢复用户';
            }

            AdminUser::setOneById($admin_id, [
                'state' => $state,
            ]);
            Util::update_admin_sub($admin_id);

            AdminRecord::createOne([
                'admin_id' => $admin_id,
                'room_id' => 0,    //  INTEGER  操作相关  room_id  尽可能 尝试记录
                //  INTEGER  操作者  admin_id  尽可能 尝试记录
                'op_type' => 3,    //  SMALLINT  操作类型  0 未知  1 登录  2 登出 3 其他操作
                'op_desc' => $op_desc,    //  VARCHAR(255)  field op_desc
                'op_ref' => $this->getRequest()->getHttpReferer(),    //  VARCHAR(255)  field op_ref
                'op_url' => $this->fullUrl(),    //  VARCHAR(255)  field op_url
                'op_args' => self::funcGetArgs(func_get_args(), __METHOD__),    //  TEXT  本次操作的 参数
                'op_ip' => $ip,    //  VARCHAR(20)  field op_ip
                'op_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
                'op_method' => __METHOD__,  // VARCHAR(255)  field op_method
                'op_admin_id' => $this->admin_id,    //  INTEGER  操作者  admin_id  尽可能 尝试记录
            ]);
        }

        $rst = ['msg' => '修改成功'];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    public function getParentVLimitAllRoomUsed($parent_id)
    {
        $parent_id = intval($parent_id);
        self::_checkBeyondAdmin($this->admin_id, $parent_id, "权限错误 cur_admin_id:{$this->admin_id}, parent_id:{$parent_id}");
        $vlimit_used = self::_sumRoomViewLimitByParent($parent_id);
        return [
            'vlimit_all_room' => AdminUser::vlimit_all_room($parent_id),
            'vlimit_used' => !empty($vlimit_used) ? intval($vlimit_used) : 0,
        ];
    }

    public function getAgentVLimitOnlineNumUsed($agent_id)
    {
        $agent_id = intval($agent_id);
        self::_checkBeyondAdmin($this->admin_id, $agent_id, "权限错误 cur_admin_id:{$this->admin_id}, agent_id:{$agent_id}");
        $vlimit_used = self::_sumParentVLimitAllRoomByAgent($agent_id);
        return [
            'vlimit_online_num' => AdminUser::vlimit_online_num($agent_id),
            'vlimit_used' => !empty($vlimit_used) ? intval($vlimit_used) : 0,
        ];
    }

    /**
     * 检查 admin 帐号 是否可用
     * @param string $name 帐号  可以使用中文
     * @return array ['msg' => '创建成功'];
     * @throws ApiParamsError
     */
    public function isFreeAdminName($name)
    {
        $name = trim($name);
        if (empty($name)) {
            throw new ApiParamsError("参数错误");
        }

        $tmp_admin_id = AdminUser::checkItem($name, 'name');
        if (!empty($tmp_admin_id)) {
            throw new ApiParamsError("该账号已被占用");
        }

        $rst = ['msg' => '该账号可用'];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 设置 admin 权限访问列表 ACL
     * @param int $admin_id admin ID
     * @param array $aclMap
     * @return array
     */
    public function setAdminACL($admin_id, array $aclMap = [])
    {
        $admin_id = intval($admin_id);
        self::_checkBeyondAdmin($this->admin_id, $admin_id, "权限错误 cur_admin_id:{$this->admin_id}, admin_id:{$admin_id}");

        foreach (self::ALL_ACL_TYPE as $tag) {
            if ($tag == 'room' && !self::_isSub($admin_id)) {
                continue;
            }
            $acl = !empty($aclMap[$tag]) ? (array)$aclMap[$tag] : [];
            AdminAccessControl::_update(AdminAccessControl::tableBuilder([
                'admin_id' => $admin_id,
                'access_type' => $tag,
            ]), [
                'state' => StateEnum::DELETED_VALUE
            ]);

            foreach ($acl as $ck => $cv) {
                $state = !empty($cv) ? StateEnum::NORMAL_VALUE : StateEnum::FROZEN_VALUE;
                AdminAccessControl::upsertItem([
                    'admin_id' => $admin_id,
                    'access_type' => $tag,
                    'access_value' => $ck
                ], [
                    'admin_id' => $admin_id,
                    'access_type' => $tag,
                    'access_value' => $ck,
                    'state' => $state
                ]);
            }
            self::_loadACLByAdmin($admin_id, $tag, -1);

            if ($tag == 'room' && self::_isSub($admin_id)) {
                Util::roomSub($admin_id, -1);
            }
        }

        $rst = ['msg' => '修改成功'];
        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 获取 admin 权限访问列表 ACL
     * @param int $admin_id admin ID
     * @param string $type 默认为空 表示获取所有 ACL
     * @return array
     */
    public function getAdminACL($admin_id, $type = '')
    {
        $admin_id = intval($admin_id);
        $type = trim($type);
        $type = !empty($type) && in_array($type, self::ALL_ACL_TYPE) ? [$type] : self::ALL_ACL_TYPE;

        self::_checkBeyondAdmin($this->admin_id, $admin_id, "权限错误 cur_admin_id:{$this->admin_id}, admin_id:{$admin_id}");

        $data = [];
        foreach ($type as $tag) {
            $tmp = self::_loadACLByAdmin($admin_id, $tag);
            $data[$tag] = Util::build_map($tmp);
        }

        $appRouter = AdminController::_getAppRouterByAdminType(AdminUser::admin_type($admin_id));
        $menuMap = AdminController::_buildMenuACLByDeps($appRouter, $admin_id);
        $roomMap = [];
        if (self::_isSub($admin_id)) {
            $roomMap = LiveRoom::selectItem(0, 0, ['room_id', 'asc'], [
                'state' => [StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE],
                'admin_id' => AdminUser::parent_id($admin_id),
            ]);
        }
        $rst = [
            'msg' => '获取成功',
            'menuMap' => $menuMap,
            'roomMap' => $roomMap,
            'data' => $data,
        ];
        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    public function newSuperAdmin($name, $pasw, $title = '', $register_from = '手动添加')
    {
        $cur_admin_id = $this->auth()->id();
        if (!self::_isSuper($cur_admin_id) || !AdminUser::checkOne($cur_admin_id, 'admin_slug', AdminUser::SLUG_SUPER)) {
            throw new ApiParamsError("权限错误");
        }

        $name = trim($name);
        $pasw = trim($pasw);
        $title = trim($title);

        $title = !empty($title) ? $title : $name;

        if (empty($name) || empty($title) || empty($pasw)) {
            throw new ApiParamsError("参数错误");
        }


        $tmp_admin_id = AdminUser::checkItem($name, 'name');
        if (!empty($tmp_admin_id)) {
            throw new ApiParamsError("该账号已被占用");
        }

        $agent_admin_id = AdminUser::createOne([
            'name' => $name,
            'title' => $title,
            'pasw' => $pasw,
            'agent_id' => 0,
            'parent_id' => 0,
            'avator' => self::$default_avator,
            'state' => StateEnum::NORMAL_VALUE,
            'register_from' => $register_from,
            'admin_slug' => '',
            'admin_type' => AdminTypeEnum::SUPER_VALUE,
        ]);
        Util::update_admin_sub($agent_admin_id);

        $ip = $this->client_ip();
        $op_desc = '创建客服';

        AdminRecord::createOne([
            'admin_id' => $agent_admin_id,
            'room_id' => 0,    //  INTEGER  操作相关  room_id  尽可能 尝试记录
            //  INTEGER  操作者  admin_id  尽可能 尝试记录
            'op_type' => 3,    //  SMALLINT  操作类型  0 未知  1 登录  2 登出 3 其他操作
            'op_desc' => $op_desc,    //  VARCHAR(255)  field op_desc
            'op_ref' => $this->getRequest()->getHttpReferer(),    //  VARCHAR(255)  field op_ref
            'op_url' => $this->fullUrl(),    //  VARCHAR(255)  field op_url
            'op_args' => self::funcGetArgs(func_get_args(), __METHOD__),    //  TEXT  本次操作的 参数
            'op_ip' => $ip,    //  VARCHAR(20)  field op_ip
            'op_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
            'op_method' => __METHOD__,  // VARCHAR(255)  field op_method
            'op_admin_id' => $this->admin_id,    //  INTEGER  操作者  admin_id  尽可能 尝试记录
        ]);

        $rst = ['msg' => '创建成功', 'data' => GQLAdminUser::_execQueryAdmin($agent_admin_id)];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 创建新的代理 admin
     * @param string $name 帐号
     * @param string $pasw 密码
     * @param string $title 名称
     * @param string $register_from
     * @param string $admin_slug
     * @return array
     * @throws ApiParamsError
     */
    public function newAgentAdmin($name, $pasw, $title = '', $register_from = '手动添加', $admin_slug = AdminUser::SLUG_AGENT)
    {
        $name = trim($name);
        $pasw = trim($pasw);
        $title = trim($title);
        $admin_slug = trim($admin_slug);

        $title = !empty($title) ? $title : $name;

        if (empty($name) || empty($title) || empty($pasw)) {
            throw new ApiParamsError("参数错误");
        }

        $cur_admin_id = $this->auth()->id();
        if (!self::_isSuper($cur_admin_id)) {
            throw new ApiParamsError("权限错误");
        }

        $tmp_admin_id = AdminUser::checkItem($name, 'name');
        if (!empty($tmp_admin_id)) {
            throw new ApiParamsError("该账号已被占用");
        }

        $agent_admin_id = AdminUser::createOne([
            'name' => $name,
            'title' => $title,
            'pasw' => $pasw,
            'agent_id' => 0,
            'parent_id' => 0,
            'avator' => self::$default_avator,
            'state' => StateEnum::NORMAL_VALUE,
            'register_from' => $register_from,
            'admin_slug' => $admin_slug,
            'limit_onlinenumber_over_price' => Util::content('sitecfg.over_price'),
            'account_credit' => $admin_slug == AdminUser::SLUG_AGENT ? Util::content('sitecfg.agent_credit') : 0.0,
            'admin_type' => AdminTypeEnum::AGENT_VALUE,
        ]);
        Util::update_admin_sub($agent_admin_id);

        $ip = $this->client_ip();
        $op_desc = '创建代理';

        AdminRecord::createOne([
            'admin_id' => $agent_admin_id,
            'room_id' => 0,    //  INTEGER  操作相关  room_id  尽可能 尝试记录
            //  INTEGER  操作者  admin_id  尽可能 尝试记录
            'op_type' => 3,    //  SMALLINT  操作类型  0 未知  1 登录  2 登出 3 其他操作
            'op_desc' => $op_desc,    //  VARCHAR(255)  field op_desc
            'op_ref' => $this->getRequest()->getHttpReferer(),    //  VARCHAR(255)  field op_ref
            'op_url' => $this->fullUrl(),    //  VARCHAR(255)  field op_url
            'op_args' => self::funcGetArgs(func_get_args(), __METHOD__),    //  TEXT  本次操作的 参数
            'op_ip' => $ip,    //  VARCHAR(20)  field op_ip
            'op_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
            'op_method' => __METHOD__,  // VARCHAR(255)  field op_method
            'op_admin_id' => $this->admin_id,    //  INTEGER  操作者  admin_id  尽可能 尝试记录
        ]);

        $rst = ['msg' => '创建成功', 'data' => GQLAdminUser::_execQueryAdmin($agent_admin_id)];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 创建新的 子账号 admin
     * @param int $parent_id 客户 admin ID
     * @param string $name 帐号
     * @param string $pasw 密码
     * @param string $title 名称
     * @return array
     * @throws ApiParamsError
     */
    public function newSubAdmin($parent_id, $name, $pasw, $title = '')
    {
        $parent_id = intval($parent_id);
        $name = trim($name);
        $pasw = trim($pasw);
        $title = trim($title);

        self::_checkBeyondAdmin($this->admin_id, $parent_id, "权限错误 cur_admin_id:{$this->admin_id}, admin_id:{$parent_id}");


        $title = !empty($title) ? $title : $name;

        if (empty($name) || empty($title) || empty($pasw)) {
            throw new ApiParamsError("参数错误");
        }

        $tmp_admin_id = AdminUser::checkItem($name, 'name');
        if (!empty($tmp_admin_id)) {
            throw new ApiParamsError("该账号已被占用");
        }
        $agent_id = AdminUser::agent_id($parent_id);
        $sub_admin_id = AdminUser::createOne([
            'name' => $name,
            'title' => $title,
            'pasw' => $pasw,
            'agent_id' => $agent_id,
            'parent_id' => $parent_id,
            'avator' => self::$default_avator,
            'state' => StateEnum::NORMAL_VALUE,
            'register_from' => '后台创建',
            'admin_type' => AdminTypeEnum::SUB_VALUE,
        ]);
        Util::update_admin_sub($sub_admin_id);

        $ip = $this->client_ip();
        $op_desc = '创建子账号';

        AdminRecord::createOne([
            'admin_id' => $sub_admin_id,
            'room_id' => 0,    //  INTEGER  操作相关  room_id  尽可能 尝试记录
            //  INTEGER  操作者  admin_id  尽可能 尝试记录
            'op_type' => 3,    //  SMALLINT  操作类型  0 未知  1 登录  2 登出 3 其他操作
            'op_desc' => $op_desc,    //  VARCHAR(255)  field op_desc
            'op_ref' => $this->getRequest()->getHttpReferer(),    //  VARCHAR(255)  field op_ref
            'op_url' => $this->fullUrl(),    //  VARCHAR(255)  field op_url
            'op_args' => self::funcGetArgs(func_get_args(), __METHOD__),    //  TEXT  本次操作的 参数
            'op_ip' => $ip,    //  VARCHAR(20)  field op_ip
            'op_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
            'op_method' => __METHOD__,  // VARCHAR(255)  field op_method
            'op_admin_id' => $this->admin_id,    //  INTEGER  操作者  admin_id  尽可能 尝试记录
        ]);

        $rst = ['msg' => '创建成功', 'data' => GQLAdminUser::_execQueryAdmin($sub_admin_id)];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 为代理创建 客户
     * @param int $agent_id 代理 id
     * @param string $name 帐号
     * @param string $pasw 密码
     * @param int $vlimit_all_room
     * @param string $title 名称
     * @param string $expiration_date
     * @param int $create_mcs 是否自动创建 MCS 帐号
     * @param string $admin_slug
     * @return array ['admin_id' => $parent_admin_id, 'msg' => '创建成功'];
     * @throws ApiParamsError
     */
    public function newParentAdmin($agent_id, $name, $pasw, $vlimit_all_room = 0, $title = '', $expiration_date = '0000-00-00 00:00:00', $create_mcs = 0, $admin_slug = '')
    {
        $agent_id = intval($agent_id);
        $name = trim($name);
        $pasw = trim($pasw);
        $vlimit_all_room = $vlimit_all_room > 0 ? intval($vlimit_all_room) : 0;
        $title = trim($title);
        $expiration_date = trim($expiration_date);
        $create_mcs = !empty($create_mcs) ? 1 : 0;
        $admin_slug = AdminUser::SLUG_PARENT == $admin_slug ? $admin_slug : '';

        self::_checkBeyondAdmin($this->admin_id, $agent_id, "权限错误 cur_admin_id:{$this->admin_id}, admin_id:{$agent_id}");
        self::_checkAgentNLimitCountParent($agent_id, "客户数量超出限制 最大值:" . AdminUser::nlimit_count_parent($agent_id));
        self::_checkAgentVLimitAllRoom($agent_id, $vlimit_all_room, "客户并发总数超出限制 最大值:" . AdminUser::vlimit_per_parent($agent_id));

        $title = !empty($title) ? $title : $name;
        $expiration_date = !empty($expiration_date) && $expiration_date != '0000-00-00 00:00:00' ? date('Y-m-d H:i:s', strtotime($expiration_date)) : '0000-00-00 00:00:00';

        if (empty($name) || empty($title) || empty($pasw) || empty($title) || empty($expiration_date) || $vlimit_all_room < 0) {
            throw new ApiParamsError("参数错误");
        }

        if (!in_array($agent_id, self::$_skip_vlimit_online_num_agent_list)) {
            $can_use = AdminUser::vlimit_online_num($agent_id) > 0 ? ", 剩余可用:" . self::_maxCanUseVLimitOnlineNumByAgent($agent_id) : '';
            self::_checkParentVLimitOnlineNumForParent($agent_id, $vlimit_all_room, 0, "客户并发总数超出限制 总计:" . AdminUser::vlimit_online_num($agent_id) . ", 已使用:" . self::_sumParentVLimitAllRoomByAgent($agent_id) . $can_use);
        }

        $tmp_admin_id = AdminUser::checkItem($name, 'name');
        if (!empty($tmp_admin_id)) {
            throw new ApiParamsError("该账号已被占用");
        }
        if (!Util::str_cmp($expiration_date, '0000-00-00 00:00:00')) {
            $expiration_time = strtotime($expiration_date);
            if (empty($expiration_time)) {
                throw new ApiParamsError("参数错误");
            }
            if ($expiration_time <= time()) {
                throw new ApiParamsError("有效期必须大于当前时刻");
            }
        }

        $data = [
            'name' => $name,
            'title' => $title,
            'pasw' => $pasw,
            'vlimit_all_room' => $vlimit_all_room,
            'vlimit_online_num' => $vlimit_all_room,
            'expiration_date' => $expiration_date,
            'agent_id' => $agent_id,
            'avator' => self::$default_avator,
            'register_from' => '后台创建',
            'state' => StateEnum::NORMAL_VALUE,
            'admin_slug' => $admin_slug,
            'admin_type' => AdminTypeEnum::PARENT_VALUE,
        ];
        if (AdminUser::SLUG_PARENT == $admin_slug) {
            $data = array_merge($data, [
                'nlimit_count_room' => Util::content('sitecfg.parent_count_room'),
                'nlimit_count_sub' => Util::content('sitecfg.parent_count_sub'),
                'account_credit' => Util::content('sitecfg.parent_credit'),
                'limit_onlinenumber_over_price' => Util::content('sitecfg.over_price'),
            ]);
        }
        $parent_admin_id = AdminUser::createOne($data);
        Util::update_admin_sub($parent_admin_id);

        if (!empty($create_mcs)) {
            $stream_name = '默认直播账号';
            $mcs_account = 'xdy' . rand(100000, 999999);
            $mcs_password = 'xdy123456';
            $api = StreamMgr::_createFromApi($this);
            $ret = $api->newMcsStream($parent_admin_id, $stream_name, $mcs_account, $mcs_password);
            $msg = "auto create mcs parent_id:{$parent_admin_id}, ret:" . json_encode($ret);
            self::info($msg, __METHOD__, __CLASS__, __LINE__);
        }

        $ip = $this->client_ip();
        $op_desc = '创建客户';

        AdminRecord::createOne([
            'admin_id' => $parent_admin_id,
            'room_id' => 0,    //  INTEGER  操作相关  room_id  尽可能 尝试记录
            //  INTEGER  操作者  admin_id  尽可能 尝试记录
            'op_type' => 3,    //  SMALLINT  操作类型  0 未知  1 登录  2 登出 3 其他操作
            'op_desc' => $op_desc,    //  VARCHAR(255)  field op_desc
            'op_ref' => $this->getRequest()->getHttpReferer(),    //  VARCHAR(255)  field op_ref
            'op_url' => $this->fullUrl(),    //  VARCHAR(255)  field op_url
            'op_args' => self::funcGetArgs(func_get_args(), __METHOD__),    //  TEXT  本次操作的 参数
            'op_ip' => $ip,    //  VARCHAR(20)  field op_ip
            'op_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
            'op_method' => __METHOD__,  // VARCHAR(255)  field op_method
            'op_admin_id' => $this->admin_id,    //  INTEGER  操作者  admin_id  尽可能 尝试记录
        ]);

        $rst = ['msg' => '创建成功', 'data' => GQLAdminUser::_execQueryAdmin($parent_admin_id)];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 同步 admin 信息
     * @param int $admin_id admin ID
     * @return array ['msg' => '同步成功'];
     */
    public function syncAdminInfo($admin_id)
    {
        $admin_id = intval($admin_id);

        self::_checkBeyondAdmin($this->admin_id, $admin_id, "权限错误 cur_admin_id:{$this->admin_id}, admin_id:{$admin_id}");


        GQLAdminUser::_syncQueryAdmin($admin_id);

        $rst = ['msg' => '同步成功'];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 批量修改 admin 状态
     * @param array $admin_ids
     * @param  int $state 参见 StateEnum   ['FROZEN' => 7, 'UNKNOWN' => 0, 'DELETED' => 9, 'NORMAL' => 1]
     * @return array
     * @throws ApiParamsError
     */
    public function stateAdminBatch($admin_ids = [], $state)
    {
        $state = intval($state);
        $admin_ids = !empty($admin_ids) ? (array)$admin_ids : [];

        if (empty($state) || !in_array($state, array_values(StateEnum::ALL_ENUM_MAP)) || $state == StateEnum::NOTDEL_VALUE) {
            throw new ApiParamsError("参数错误 state:{$state}");
        }
        if (empty($admin_ids)) {
            throw new ApiParamsError("参数错误");
        }
        foreach ($admin_ids as $admin_id) {
            $this->stateAdmin($admin_id, $state);
        }
        $rst = ['msg' => '修改成功'];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * @param int $admin_id admin ID
     * @param string $pasw 密码
     * @return array
     * @throws ApiParamsError
     */
    public function paswAdmin($admin_id, $pasw)
    {
        $pasw = trim($pasw);
        $admin_id = intval($admin_id);

        if (empty($pasw)) {
            throw new ApiParamsError("参数错误");
        }

        self::_checkBeyondAdmin($this->admin_id, $admin_id, "权限错误 cur_admin_id:{$this->admin_id}, admin_id:{$admin_id}", false);

        $ip = $this->client_ip();
        $op_desc = '重置用户密码';

        AdminUser::setOneById($admin_id, [
            'pasw' => $pasw,
        ]);

        AdminRecord::createOne([
            'admin_id' => $admin_id,
            'room_id' => 0,    //  INTEGER  操作相关  room_id  尽可能 尝试记录
            //  INTEGER  操作者  admin_id  尽可能 尝试记录
            'op_type' => 3,    //  SMALLINT  操作类型  0 未知  1 登录  2 登出 3 其他操作
            'op_desc' => $op_desc,    //  VARCHAR(255)  field op_desc
            'op_ref' => $this->getRequest()->getHttpReferer(),    //  VARCHAR(255)  field op_ref
            'op_url' => $this->fullUrl(),    //  VARCHAR(255)  field op_url
            'op_args' => self::funcGetArgs(func_get_args(), __METHOD__),    //  TEXT  本次操作的 参数
            'op_ip' => $ip,    //  VARCHAR(20)  field op_ip
            'op_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
            'op_method' => __METHOD__,  // VARCHAR(255)  field op_method
            'op_admin_id' => $this->admin_id,    //  INTEGER  操作者  admin_id  尽可能 尝试记录
        ]);

        $rst = ['msg' => '重置成功'];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 根据 id 修改 后台用户信息  需要比自身高一级的权限
     * @param int $admin_id admin ID
     * @param string $avator 默认为空  表示不修改
     * @param string $name 帐号 默认为空  表示不修改
     * @param string $title 名称 默认为空  表示不修改
     * @param string $expiration_date 默认为空  表示不修改
     * @param string $email 默认为空  表示不修改
     * @param string $cellphone 默认为空  表示不修改
     * @param string $company 默认为空  表示不修改
     * @param string $industry 默认为空  表示不修改
     * @param string $admin_note 默认为空  表示不修改
     * @param null $admin_slug
     * @param null $vlimit_online_num INTEGER  账号  最大并发数限制 一般用于客户 和 代理，0为无限制
     * @param int $vlimit_per_parent INTEGER  对下属客户 最大并发数限制  一般用于代理，0为无限制  默认为空  表示不修改
     * @param int $vlimit_per_room INTEGER  对下属每个频道 最大并发数限制  一般用于代理，0为无限制  默认为空  表示不修改
     * @param int $vlimit_all_room INTEGER  对下属 客户 总数限制  一般用于代理，0为无限制  默认为空  表示不修改
     * @param int $nlimit_count_parent INTEGER  对下属所有频道 最大并发数限制 之和 一般用于客户，0为无限制  默认为空  表示不修改
     * @param int $nlimit_count_room INTEGER  对下属 频道 总数限制  一般用于客户，0为无限制  默认为空  表示不修改
     * @param int $nlimit_count_sub INTEGER  对下属 子账号 总数限制 一般用于客户，0为无限制  默认为空  表示不修改
     * @param int $nlimit_count_player INTEGER  对下属 播放器 总数限制 一般用于客户，0为无限制  默认为空  表示不修改
     * @param int $nlimit_count_stream INTEGER  对下属 视频流 总数限制 一般用于客户，0为无限制  默认为空  表示不修改
     * @param null $cname_host
     * @param null $cname_cdn
     * @param null $mcs_vhost
     * @param null $cfg_site_ico
     * @param null $cfg_admin_logo
     * @param null $cfg_admin_mlogo
     * @param null $cfg_login_logo
     * @param null $business_belong
     * @return array ['msg' => '修改成功'];
     * @throws ApiParamsError
     */
    public function setAdminInfo($admin_id, $avator = null, $name = null, $title = null, $expiration_date = null, $email = null, $cellphone = null, $company = null, $industry = null, $admin_note = null, $admin_slug = null, $vlimit_online_num = null, $vlimit_per_parent = null, $vlimit_per_room = null, $vlimit_all_room = null, $nlimit_count_parent = null, $nlimit_count_room = null, $nlimit_count_sub = null, $nlimit_count_player = null, $nlimit_count_stream = null, $cname_host = null, $cname_cdn = null, $mcs_vhost = null, $cfg_site_ico = null, $cfg_admin_logo = null, $cfg_admin_mlogo = null, $cfg_login_logo = null, $business_belong = null)
    {
        $admin_id = intval($admin_id);
        self::_checkBeyondAdmin($this->admin_id, $admin_id, "权限错误 cur_admin_id:{$this->admin_id}, admin_id:{$admin_id}", false);

        $args = self::_getMethodArgs(func_get_args(), __METHOD__);
        unset($args['admin_id']);
        $limit_map = [];
        if (self::_isAgent($admin_id)) {
            $limit_map = ['vlimit_online_num', 'vlimit_per_parent', 'vlimit_per_room', 'nlimit_count_parent'];
        } elseif (self::_isParent($admin_id)) {
            $limit_map = ['vlimit_online_num', 'vlimit_all_room', 'nlimit_count_room', 'nlimit_count_sub', 'nlimit_count_player', 'nlimit_count_stream'];
        }

        $admin_config = [];
        foreach ($args as $argk => $argv) {
            if (is_null($argv)) {
                $args[$argk] = $argv;
            } elseif (Util::stri_startwith($argk, 'nlimit_') || Util::stri_startwith($argk, 'vlimit_')) {
                if (in_array($argk, $limit_map)) {  // nlimit_ 或 vlimit_ 前缀的  根据 目标管理员类型 只设置有效的的选项
                    $args[$argk] = $argv > 0 ? intval($argv) : 0;
                }
            } elseif (Util::stri_startwith($argk, 'cfg_')) {
                $key = substr($argk, 4);
                $admin_config[$key] = trim("{$argv}");   // cfg_ 前缀的 存入 admin_config  字段 以 json 格式保存
            } else {
                $args[$argk] = trim("{$argv}");
            }
        }

        $update = [];
        if (!empty($admin_config)) {
            $last_config_json = AdminUser::admin_config($admin_id, '');
            $last_config = !empty($last_config_json) ? json_decode($last_config_json, true) : [];
            $update['admin_config'] = json_encode(array_merge($last_config, $admin_config));
            foreach ($admin_config as $k => $v) {
                unset($args[$k]);
            }
        }

        if (!is_null($args['business_belong']) && AdminUser::business_belong($admin_id) != $args['business_belong']) {
            if ($args['business_belong'] > 0) {
                if (!AdminUser::checkOne($args['business_belong'], 'admin_slug', AdminUser::SLUG_BUSINESS)) {
                    throw new ApiParamsError("商务信息不正确");
                }
                $update['admin_config'] = $args['business_belong'];
            }
        }

        if (!is_null($args['name']) && AdminUser::name($admin_id) != $args['name']) {
            $tmp_admin_id = AdminUser::checkItem($args['name'], 'name');
            if (!empty($tmp_admin_id) && $tmp_admin_id != $admin_id) {
                throw new ApiParamsError("该账号已被占用");
            }
            $update['name'] = $args['name'];
        }

        if (!is_null($args['admin_slug']) && AdminUser::admin_slug($admin_id) != $args['admin_slug']) {
            if ((self::_isAgent($admin_id) && in_array($args['admin_slug'], [AdminUser::SLUG_SELF, AdminUser::SLUG_AGENT])) ||
                (self::_isParent($admin_id) && in_array($args['admin_slug'], [AdminUser::SLUG_PARENT]))) {
                $update['admin_slug'] = $args['admin_slug'];
            }
        }

        if (!is_null($args['expiration_date'])) {
            $args['expiration_date'] = !empty($args['expiration_date']) && $args['expiration_date'] != '0000-00-00 00:00:00' ? Util::ymdhis($args['expiration_date']) : '0000-00-00 00:00:00';
            if (AdminUser::expiration_date($admin_id) != $args['expiration_date']) {
                if ($args['expiration_date'] == '0000-00-00 00:00:00') {
                    $update['expiration_date'] = $args['expiration_date'];
                } else {
                    $expiration_date_int = strtotime($args['expiration_date']);
                    if ($expiration_date_int > 0) {
                        if ($expiration_date_int <= time()) {
                            throw new ApiParamsError("有效期必须大于当前时刻");
                        }
                        $update['expiration_date'] = $args['expiration_date'];
                    }
                }
            }
        }

        if (self::_isParent($admin_id)) {
            $parent_id = $admin_id;
            $agent_id = AdminUser::agent_id($admin_id);
            $args['vlimit_all_room'] = !is_null($args['vlimit_all_room']) ? $args['vlimit_all_room'] : $args['vlimit_online_num'];
            if (!is_null($args['vlimit_all_room']) && AdminUser::vlimit_all_room($admin_id) != $args['vlimit_all_room']) {
                self::_checkAgentVLimitAllRoom($agent_id, $args['vlimit_all_room'], "客户并发总数超出限制 最大值:" . AdminUser::vlimit_per_parent($agent_id));

                if (!in_array($agent_id, self::$_skip_vlimit_online_num_agent_list)) {
                    $can_use = AdminUser::vlimit_online_num($agent_id) > 0 ? ", 剩余可用:" . self::_maxCanUseVLimitOnlineNumByAgent($agent_id, $parent_id) : '';
                    self::_checkParentVLimitOnlineNumForParent($agent_id, $args['vlimit_all_room'], $parent_id, "客户并发总数超出限制 总计:" . AdminUser::vlimit_online_num($agent_id) . ", 已使用:" . self::_sumParentVLimitAllRoomByAgent($agent_id) . $can_use);
                }

                $update['vlimit_all_room'] = $args['vlimit_all_room'];
                $update['vlimit_online_num'] = $update['vlimit_all_room'];
            }
        }

        foreach ($args as $argk => $argv) {
            if (!is_null($argv) && AdminUser::valueOneById($admin_id, $argk, '') != $argv && !isset($update[$argk])) {
                $update[$argk] = $argv;
            }
        }

        if (!empty($update)) {
            AdminUser::setOneById($admin_id, $update);
            Util::update_admin_sub($admin_id);

            $ip = $this->client_ip();
            $op_desc = '修改用户信息';

            AdminRecord::createOne([
                'admin_id' => $admin_id,
                'room_id' => 0,    //  INTEGER  操作相关  room_id  尽可能 尝试记录
                //  INTEGER  操作者  admin_id  尽可能 尝试记录
                'op_type' => 3,    //  SMALLINT  操作类型  0 未知  1 登录  2 登出 3 其他操作
                'op_desc' => $op_desc,    //  VARCHAR(255)  field op_desc
                'op_ref' => $this->getRequest()->getHttpReferer(),    //  VARCHAR(255)  field op_ref
                'op_url' => $this->fullUrl(),    //  VARCHAR(255)  field op_url
                'op_args' => $update,    //  TEXT  本次操作的 参数
                'op_ip' => $ip,    //  VARCHAR(20)  field op_ip
                'op_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
                'op_method' => __METHOD__,  // VARCHAR(255)  field op_method
                'op_admin_id' => $this->admin_id,    //  INTEGER  操作者  admin_id  尽可能 尝试记录
            ]);
        }

        $rst = ['msg' => '修改成功'];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 修改用户基本信息
     * @param int $admin_id admin ID
     * @param string $title 名称
     * @param string $company
     * @param string $industry
     * @return array ['msg' => '修改成功'];
     * @throws ApiAuthBeyondError
     */
    public function editAdminInfo($admin_id, $title, $company, $industry)
    {
        $admin_id = intval($admin_id);
        self::_checkBeyondAdmin($this->admin_id, $admin_id, "权限错误 cur_admin_id:{$this->admin_id}, admin_id:{$admin_id}");

        $title = trim($title);
        $company = trim($company);
        $industry = trim($industry);

        if ($title == AdminUser::title($admin_id) && $company == AdminUser::company($admin_id) && $industry == AdminUser::industry($admin_id)) {
            // 全部相同 跳过修改
        } else {
            AdminUser::setOneById($admin_id, [
                'title' => $title,
                'company' => $company,
                'industry' => $industry,
            ]);
            Util::update_admin_sub($admin_id);

            $ip = $this->client_ip();
            $op_desc = '修改个人信息';
            AdminRecord::createOne([
                'admin_id' => $admin_id,
                'room_id' => 0,    //  INTEGER  操作相关  room_id  尽可能 尝试记录
                //  INTEGER  操作者  admin_id  尽可能 尝试记录
                'op_type' => 3,    //  SMALLINT  操作类型  0 未知  1 登录  2 登出 3 其他操作
                'op_desc' => $op_desc,    //  VARCHAR(255)  field op_desc
                'op_ref' => $this->getRequest()->getHttpReferer(),    //  VARCHAR(255)  field op_ref
                'op_url' => $this->fullUrl(),    //  VARCHAR(255)  field op_url
                'op_args' => self::funcGetArgs(func_get_args(), __METHOD__),    //  TEXT  本次操作的 参数
                'op_ip' => $ip,    //  VARCHAR(20)  field op_ip
                'op_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
                'op_method' => __METHOD__,  // VARCHAR(255)  field op_method
                'op_admin_id' => $this->admin_id,    //  INTEGER  操作者  admin_id  尽可能 尝试记录
            ]);
        }

        $rst = ['msg' => '修改成功'];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

}