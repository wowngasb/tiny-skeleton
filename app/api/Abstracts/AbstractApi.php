<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/5/7 0007
 * Time: 10:16
 */

namespace app\api\Abstracts;


use app\api\GraphQL_\Enum\AdminTypeEnum;
use app\api\GraphQL_\Enum\StateEnum;
use app\App;
use app\Controller;
use app\Exception\ApiAuthBeyondError;
use app\Exception\ApiAuthError;
use app\Exception\ApiCheckLimitError;
use app\Exception\ApiParamsError;
use app\Exception\NeverRunAtHereError;
use app\Libs\AdminAuth;
use app\Model\AdminAccessControl;
use app\Model\AdminUser;
use app\Model\LiveRoom;
use app\Model\PlayerBase;
use app\Model\RoomRunning;
use app\Model\RoomRunningDms;
use app\Model\RoomRunningDmsRef;
use app\Model\RoomRunningDmsSum;
use app\Model\RoomRunningSum;
use app\Model\SiteMgrUser;
use app\Model\StreamBase;
use app\Model\XdyOrder;
use app\Model\XdyProduct;
use app\Util;
use Tiny\Abstracts\AbstractApi as _AbstractApi;
use Tiny\Interfaces\AuthInterface;
use Tiny\OrmQuery\Q;

abstract class AbstractApi extends _AbstractApi
{

    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);
        if (!empty($params['params_'])) {
            $params = array_merge($params, json_decode($params['params_'], true));
        }
        self::$_instance = $this;

        return $params;
    }

    protected static function _D($data, $tags = null, $ignoreTraceCalls = 0)
    {
        $request = Controller::_getRequestByCtx();
        if (!empty($request)) {
            $tags = $request->debugTag($tags);
        }
        App::_D($data, $tags, $ignoreTraceCalls);
    }

    const DEFAULT_HLS = "http://test-live.xdysoft.com/xdylive/stream.m3u8";

    const DEFAULT_RTMP = "rtmp://test-live.xdysoft.com/xdylive/stream";

    const DEFAULT_PLAYER_TYPE = 'hts_player';

    const ALL_ACL_TYPE = ['menu', 'room'];

    const SHORT_CACHE_TIME = 5;

    public static $default_avator = 'https://ss1.bdstatic.com/70cFvXSh_Q1YnxGkpoWK1HF6hhy/it/u=3448484253,3685836170&fm=27&gp=0.jpg';

    /**
     * 尝试 admin_slug 设置为 xdy_self 的 表示为 自营代理  设置注册的账号为代理下
     * @param int $agent_id
     * @return int
     */
    public static function _tryFindFirstBaseAgentId($agent_id = 0)
    {
        $agent_id = intval($agent_id);
        $agent_id = $agent_id > 0 && self::_isAgent($agent_id) && AdminUser::testAdminState($agent_id) ? $agent_id : 0;
        if (!empty($agent_id)) {
            return $agent_id;
        }

        $item = AdminUser::firstItem([
            'admin_type' => AdminTypeEnum::AGENT_VALUE,
            'state' => StateEnum::NORMAL_VALUE,
            'admin_slug' => AdminUser::SLUG_SELF,   // admin_slug 设置为 xdy_self 的 表示为 自营代理
        ], ['admin_id', 'desc']);
        if (!empty($item->admin_id)) {
            return $item->admin_id;
        }
        return 0;
    }

    public static function _tryFindFirstSuperId($super_id = 0, $timeCache = 20)
    {
        $super_id = intval($super_id);
        $super_id = $super_id > 0 && self::_isSuper($super_id) && AdminUser::testAdminState($super_id) ? $super_id : 0;
        if (!empty($super_id)) {
            return $super_id;
        }

        $item = self::_cacheDataManager(__METHOD__, self::_hashKey([
            'order' => 'asc'
        ]), function () {
            return AdminUser::firstItem([
                'admin_type' => AdminTypeEnum::SUPER_VALUE,
                'state' => StateEnum::NORMAL_VALUE,
                'admin_slug' => AdminUser::SLUG_SUPER,
            ], ['admin_id', 'asc']);
        }, function ($data) {
            return !empty($data) ? true : 5;
        }, $timeCache);

        if (!empty($item->admin_id)) {
            return $item->admin_id;
        }
        return 0;
    }

    public static function _tryGetFirstRoomIdByStreamId($stream_id)
    {
        $stream_id = intval($stream_id);
        if (empty($stream_id)) {
            return 0;
        }

        $item = LiveRoom::firstItem([
            'stream_id' => $stream_id,
            'state' => StateEnum::NORMAL_VALUE,
        ], ['room_id', 'desc']);
        if (!empty($item->room_id)) {
            return $item->room_id;
        }
        return 0;
    }

    ######################################################
    #######################  发送函数 #####################
    ######################################################


    public static function _sendSmsAuthCode($phone_num, $code)
    {
        $msg_tpl = App::config('services.yunpian.sms_tpl', '');
        if (empty($msg_tpl)) {
            return false;
        }
        $msg = str_replace("@-@", $code, $msg_tpl);
        $log_msg = "SmsAuthCode phone:{$phone_num}, msg:{$msg}";
        self::info($log_msg, __METHOD__, __CLASS__, __LINE__);
        $retStr = Util::yunPianSms($phone_num, $msg);
        return empty($retStr);
    }

    public static function _sendEmailAuthCode($to, $name, $code)
    {
        $tpl = 'finance_authcode';
        $subject = '用户验证';
        $to = [$to];
        $sub = [
            "%name%" => [$name],
            "%code%" => [$code],
            "%date%" => [date('Y年m月d日')],
        ];
        $log_msg = "EmailAuthCode email:{$to}, name:{$name}, code:{$code}";
        self::info($log_msg, __METHOD__, __CLASS__, __LINE__);

        $result = Util::sendEmailTemplate($to, $sub, $tpl, $subject);


        if ($result['result']) {
            return true;
        } else {
            return false;
        }
    }

    ######################################################
    #######################  验证函数 #####################
    ######################################################

    protected static function _validEmailAddress($email)
    {
        $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
        if (preg_match($pattern, $email)) {
            return true;
        }
        return false;
    }

    protected static function _validPhoneNum($phone_num)
    {
        if (empty($phone_num) || !preg_match('/^1([0-9]{10})$/i', $phone_num)) {
            return false;
        }
        return true;
    }

    /**
     * 正则匹配密码格式
     * @param string $password
     * @return bool
     */
    protected static function _validPassWord($password)
    {
        if (empty($password) || !preg_match("/^.{6,18}$/i", $password)) {
            return false;
        }
        return true;
    }

    ################################################################
    ############################ 不规范的辅助函数 ##########################
    ################################################################

    /**
     * @param Controller $ctrl
     * @return static
     */
    final public static function _createFromController(Controller $ctrl)
    {
        $obj = new static($ctrl->getRequest(), $ctrl->getResponse());
        $obj->_setAuth($ctrl->auth());
        $obj->beforeAction($ctrl->getRequest()->getParams());
        return $obj;
    }

    final public static function _createFromApi(AbstractApi $api)
    {
        $obj = new static($api->getRequest(), $api->getResponse());
        $obj->_setAuth($api->auth());
        $obj->beforeAction($api->getRequest()->getParams());
        return $obj;
    }


    /** @var AuthInterface */
    private $_auth = null;

    final public function auth()
    {
        if (is_null($this->_auth)) {
            $this->_auth = $this->_initAuth();
        }
        return $this->_auth;
    }

    /**
     * @return AuthInterface
     */
    protected function _initAuth()
    {
        return new AdminAuth($this);
    }

    /**
     * @param AuthInterface $auth
     */
    final public function _setAuth(AuthInterface $auth)
    {
        $this->_auth = $auth;
    }

    /** @var AbstractApi */
    public static $_instance = null;


    ################################################################
    ############################ 静态函数 ##########################
    ################################################################

    public static function _sendSyncMsgByTopicList($id, $cmd, array $topicList, array $data)
    {
        $data_msg = json_encode([
            'id' => $id,
            'cmd' => $cmd,
            'data' => $data
        ]);
        $tmp = [];
        if (App::dev()) {
            $log_msg = "sync cmd:{$cmd} id:{$id} data:{$data_msg} topic:" . json_encode($topicList);
            self::info($log_msg, __METHOD__, __CLASS__, __LINE__);
        }

        foreach ($topicList as $topic) {
            $tmp[] = Util::publish($topic, $data_msg);
        }
        return Util::allOfArray($tmp, function ($key, $val) {
            false && func_get_args();
            return $val;
        });
    }

    public static function _isAdminWithBalance($admin_id)
    {
        if (empty($admin_id)) {
            return false;
        }
        if (self::_isAgent($admin_id) && AdminUser::checkOne($admin_id, 'admin_slug', AdminUser::SLUG_AGENT)) {
            return true;
        }
        if (self::_isParent($admin_id) && AdminUser::checkOne($admin_id, 'admin_slug', AdminUser::SLUG_PARENT)) {
            return true;
        }
        return false;
    }

    public static function _getAllAdminWithSlug($admin_slug, $admin_type, $state = [1, 2])
    {
        $where = [];
        if (is_array($state)) {
            $where['state'] = Q::whereIn($state);
        } elseif (!empty($state)) {
            $where['state'] = Q::where($state);
        }
        return AdminUser::dictItem(array_merge($where, [
            'admin_slug' => $admin_slug,
            'admin_type' => $admin_type
        ]));
    }

    /**
     * 获取所有 自营 带有余额的所有客户
     * @param array $state 状态
     * @return array
     */
    public static function _getAllAdminWithBalance($state = [1, 2])
    {
        $parent_dict = self::_getAllAdminWithSlug(AdminUser::SLUG_PARENT, AdminTypeEnum::PARENT_VALUE, $state);

        $agent_dict = self::_getAllAdminWithSlug(AdminUser::SLUG_AGENT, AdminTypeEnum::AGENT_VALUE, $state);

        return array_merge($parent_dict, $agent_dict);
    }


    ################################################################
    ############################ 辅助函数 ##########################
    ################################################################


    public static function _getVHostByAdmin($admin_id)
    {
        $base_vhost = Util::content('sitecfg.default_vhost');

        $base_vhost = trim($base_vhost);
        if (empty($admin_id)) {
            return $base_vhost;
        }

        if (self::_isSuper($admin_id)) {
            return $base_vhost;
        } elseif (self::_isAgent($admin_id)) {
            $vhost = AdminUser::mcs_vhost($admin_id, '');
            $vhost = trim($vhost);
            return !empty($vhost) ? $vhost : $base_vhost;
        } elseif (self::_isParent($admin_id)) {
            $vhost = AdminUser::mcs_vhost($admin_id, '');
            $vhost = trim($vhost);
            $agent_id = AdminUser::agent_id($admin_id);
            return !empty($vhost) ? $vhost : self::_getVHostByAdmin($agent_id);
        } elseif (self::_isSub($admin_id)) {
            $parent_id = AdminUser::parent_id($admin_id);
            return self::_getVHostByAdmin($parent_id);
        }
        return $base_vhost;
    }

    public static function _getCnameByAdmin($admin_id, $default_host = '')
    {
        if (empty($admin_id)) {
            return $default_host;
        }
        if (self::_isSuper($admin_id)) {
            return $default_host;
        } elseif (self::_isAgent($admin_id)) {
            $cname = AdminUser::cname_host($admin_id, '');
            $cname = trim($cname);
            return !empty($cname) ? $cname : $default_host;
        } elseif (self::_isParent($admin_id)) {
            $cname = AdminUser::cname_host($admin_id, '');
            $cname = trim($cname);
            $agent_id = AdminUser::agent_id($admin_id);
            return !empty($cname) ? $cname : self::_getCnameByAdmin($agent_id, $default_host);
        } elseif (self::_isSub($admin_id)) {
            $parent_id = AdminUser::parent_id($admin_id);
            return self::_getCnameByAdmin($parent_id, $default_host);
        }
        return $default_host;
    }

    public static function _getAdminConfigByAdmin($admin_id, array $default_config = [])
    {
        if (empty($admin_id)) {
            return $default_config;
        }
        if (self::_isSuper($admin_id)) {
            return $default_config;
        } elseif (self::_isAgent($admin_id)) {
            $admin_config_str = AdminUser::admin_config($admin_id, '');
            $admin_config = !empty($admin_config_str) ? json_decode($admin_config_str, true) : [];
            return array_merge($default_config, $admin_config);
        } elseif (self::_isParent($admin_id)) {
            $admin_config_str = AdminUser::admin_config($admin_id, '');
            $admin_config = !empty($admin_config_str) ? json_decode($admin_config_str, true) : [];
            $agent_id = AdminUser::agent_id($admin_id);
            return self::_getAdminConfigByAdmin($agent_id, array_merge($default_config, $admin_config));
        } elseif (self::_isSub($admin_id)) {
            $parent_id = AdminUser::parent_id($admin_id);
            return self::_getAdminConfigByAdmin($parent_id, $default_config);
        }
        return $default_config;
    }

    ################################################################
    ############################ 检查函数 ##########################
    ################################################################


    public static function _isSuper($admin_id)
    {
        return AdminUser::checkOne($admin_id, 'admin_type', AdminTypeEnum::SUPER_VALUE);
    }

    public static function _isAgent($admin_id)
    {
        return AdminUser::checkOne($admin_id, 'admin_type', AdminTypeEnum::AGENT_VALUE);
    }

    public static function _isParent($admin_id)
    {
        return AdminUser::checkOne($admin_id, 'admin_type', AdminTypeEnum::PARENT_VALUE);
    }

    public static function _isSub($admin_id)
    {
        return AdminUser::checkOne($admin_id, 'admin_type', AdminTypeEnum::SUB_VALUE);
    }

    public static function _extendWhereByAdminType($table_name, array $where, $admin_id, array $extend = [])
    {
        // 超级管理员 可以使用 agent_id 进行检索 该代理 下属 数据
        $search_agent_id = !empty($extend['search_agent_id']) ? intval($extend['search_agent_id']) : 0;
        $search_admin_slug = !empty($extend['search_admin_slug']) ? Util::trimlower($extend['search_admin_slug']) : '';

        $ext_map = Util::build_map(array_keys($extend), true);
        $runningTables = [RoomRunning::tableName(), RoomRunningSum::tableName(), RoomRunningDms::tableName(), RoomRunningDmsSum::tableName(), RoomRunningDmsRef::tableName()];

        if (self::_isSuper($admin_id)) {
            if ($table_name == AdminUser::tableName()) {
                return $where;
            }
            if (!empty($search_admin_slug)) {
                if ($search_admin_slug == AdminUser::SLUG_AGENT) {
                    $slug_agent_id_map = array_keys(self::_getAllAdminWithSlug($search_admin_slug, AdminTypeEnum::AGENT_VALUE));
                    $where['admin_id#_search_admin_slug_ext_super'] = Q::whereIn($slug_agent_id_map);
                } elseif ($search_admin_slug == AdminUser::SLUG_PARENT) {
                    $slug_parent_id_map = array_keys(self::_getAllAdminWithSlug($search_admin_slug, AdminTypeEnum::PARENT_VALUE));
                    $where['admin_id#_search_admin_slug_ext_super'] = Q::whereIn($slug_parent_id_map);
                } elseif ($search_admin_slug == AdminUser::SLUG_ALL) {
                    $balance_id_map = array_keys(self::_getAllAdminWithBalance());
                    $where['admin_id#_search_admin_slug_ext_super'] = Q::whereIn($balance_id_map);
                }
            }

            if (!empty($search_agent_id)) {    // 注意 此条件不用于 检索 admin_user 表   只用于检索其他数据表  限定 admin_id 范围
                if (in_array($table_name, $runningTables)) {
                    $where['agent_id#_search_agent_id_ext_super'] = Q::where($search_agent_id);
                } else {
                    $parent_id_map = array_keys(self::_dictParentAdminByAgent($search_agent_id));
                    $where['admin_id#_search_agent_id_ext_super'] = Q::whereIn($parent_id_map);
                }
            }
            return $where;   // 自己是超级管理员  允许所有检索条件
        } elseif (self::_isAgent($admin_id)) {
            if ($table_name == AdminUser::tableName()) {
                if (!empty($ext_map['agent_id'])) {   //  只用于 admin_user 表  自己是代理  那么检索条件中的 agent_id 必须为 自己
                    $where['agent_id#_agent_id_ext_agent'] = Q::where($admin_id, '=');
                }
                if (!empty($ext_map['parent_id'])) {   // 只用于 admin_user 表  自己是代理  那么检索条件中的 parent_id 必须为 自己下属的 parent
                    $parent_id_map = array_keys(self::_dictParentAdminByAgent($admin_id));
                    $where['parent_id#_parent_id_ext_agent'] = Q::whereIn($parent_id_map);
                }
                return $where;
            }

            if (!empty($ext_map['admin_id'])) {  // 注意 此条件不用于 检索 admin_user 表   只用于检索其他数据表  限定 admin_id 范围
                if (in_array($table_name, $runningTables)) {
                    $where['agent_id#_admin_id_ext_agent'] = Q::where($admin_id);
                } else {
                    $parent_id_map = array_keys(self::_dictParentAdminByAgent($admin_id));
                    $where['admin_id#_admin_id_ext_agent'] = Q::whereIn($parent_id_map);
                }
            }
            return $where;
        } elseif (self::_isParent($admin_id)) {
            if ($table_name == AdminUser::tableName()) {
                if (!empty($ext_map['parent_id'])) {  // 只用于 admin_user 表  自己是客户  那么检索条件中的 parent_id 必须为 自己
                    $where['parent_id#_parent_id_ext_parent'] = Q::where($admin_id, '=');
                }
                return $where;
            }

            if (!empty($ext_map['admin_id'])) {   // 注意 此条件不用于 检索 admin_user 表   只用于检索其他数据表  限定 admin_id 范围
                $where['admin_id#_parent_id_ext_parent'] = Q::where($admin_id, '=');
            }
            return $where;
        } elseif (self::_isSub($admin_id)) {
            if ($table_name == AdminUser::tableName()) {
                return $where;
            }

            if (!empty($ext_map['admin_id'])) {  // 注意 此条件不用于 检索 admin_user 表   只用于检索其他数据表  限定 admin_id 范围
                $parent_id = AdminUser::parent_id($admin_id);
                $where['admin_id#_admin_id_ext_sub'] = Q::where($parent_id, '=');
            }
            if (!empty($ext_map['room_id'])) {
                $rid_set = self::_loadACLByAdmin($admin_id, 'room');
                $where['room_id#_room_id_ext_sub'] = Q::whereIn($rid_set);
            }
            if (!empty($ext_map['player_id'])) {
                false && func_get_args();// TODO 播放器 对子账号 未作分配
            }
            if (!empty($ext_map['stream_id'])) {
                false && func_get_args();// TODO 视频流 对子账号 未作分配
            }
            return $where;
        }
        throw new NeverRunAtHereError("_extendWhereByAdminType admin_type error table_name:{$table_name}, admin_id:{$admin_id}");
    }


    ################################################################
    ############################ 获取数据函数 ##########################
    ################################################################

    public static function _checkACLByAdmin($admin_id, $access_type, $access_value, $timeCache = 10)
    {
        $acl_set = self::_loadACLByAdmin($admin_id, $access_type, $timeCache);
        return in_array($access_value, $acl_set);
    }

    /**
     * @param int $admin_id
     * @param string $access_type
     * @param int $timeCache
     * @return array  去重后的 access_value set
     */
    public static function _loadACLByAdmin($admin_id, $access_type, $timeCache = 10)
    {
        return self::_cacheDataManager(__METHOD__, self::_hashKey([
            'admin_id' => $admin_id,
            'access_type' => $access_type,
        ]), function () use ($admin_id, $access_type) {
            $acl_list = AdminAccessControl::dictItem([
                'admin_id' => $admin_id,
                'access_type' => $access_type,
                'state' => StateEnum::NORMAL_VALUE,
            ]);
            return Util::set_from($acl_list, 'access_value');
        }, function ($dict) {
            return !empty($dict) ? true : self::SHORT_CACHE_TIME;
        }, $timeCache);
    }

    public static function _dictRoomBySubAdmin($sub_id, $timeCache = 10)
    {
        return self::_cacheDataManager(__METHOD__, self::_hashKey([
            'sub_id' => $sub_id,
        ]), function () use ($sub_id, $timeCache) {
            $rid_set = self::_loadACLByAdmin($sub_id, 'room', $timeCache);
            return LiveRoom::dictItem([
                'room_id' => Q::whereIn($rid_set),
            ]);
        }, function ($dict) {
            return !empty($dict) ? true : self::SHORT_CACHE_TIME;
        }, $timeCache);
    }

    public static function _dictRoomByParentAdmin($parent_id, $timeCache = 60)
    {
        return self::_cacheDataManager(__METHOD__, self::_hashKey([
            'parent_id' => $parent_id,
        ]), function () use ($parent_id) {
            return LiveRoom::dictItem([
                'room_id' => Q::where($parent_id, '='),
            ]);
        }, function ($dict) {
            return !empty($dict) ? true : self::SHORT_CACHE_TIME;
        }, $timeCache);
    }

    public static function _dictParentAdminByAgent($agent_id, $timeCache = 60)
    {
        return self::_cacheDataManager(__METHOD__, self::_hashKey([
            'agent_id' => $agent_id,
        ]), function () use ($agent_id) {
            return AdminUser::dictItem([
                'agent_id' => Q::where($agent_id, '='),
                'admin_type' => AdminTypeEnum::PARENT_VALUE,
            ]);
        }, function ($dict) {
            return !empty($dict) ? true : self::SHORT_CACHE_TIME;
        }, $timeCache);
    }

    public static function _dictSubAdminByParent($parent_id, $timeCache = 60)
    {
        return self::_cacheDataManager(__METHOD__, self::_hashKey([
            'parent_id' => $parent_id,
        ]), function () use ($parent_id) {
            return AdminUser::dictItem([
                'parent_id' => Q::where($parent_id, '='),
                'admin_type' => AdminTypeEnum::SUB_VALUE,
            ]);
        }, function ($dict) {
            return !empty($dict) ? true : self::SHORT_CACHE_TIME;
        }, $timeCache);
    }

    ###########################################################
    ######################## 权限检查相关函数 #####################
    ###########################################################


    public static function _checkBeyondAndExtendWhere($table_name, array $where, $admin_id, array $params, $method = '', $line = 0)
    {
        self::_checkBeyondParams($admin_id, $params, $method, $line);
        return self::_extendWhereByAdminType($table_name, $where, $admin_id, $params);
    }

    /**
     * @param $admin_id
     * @param array $params
     * @param string $method
     * @param int $line
     * @throws ApiAuthBeyondError
     * @throws ApiAuthError
     */
    public static function _checkBeyondParams($admin_id, array $params, $method = '', $line = 0)
    {
        $method = !empty($method) ? $method : __METHOD__;
        $line = !empty($line) ? $line : __LINE__;

        $admin_id = $admin_id > 0 ? intval($admin_id) : 0;
        if (empty($admin_id)) {
            throw new ApiAuthError("登录状态已失效，请重新登录");
        }

        $allow_keys = [
            'this_admin_id',
            'super_id',
            'agent_id',
            'parent_id',
            'admin_id',
            'room_id',
            'player_id',
            'stream_id',
        ];

        foreach ($allow_keys as $allow_key) {
            if (empty($params[$allow_key])) {
                continue;
            }

            if ($allow_key == 'this_admin_id' || $allow_key == 'admin_id' || $allow_key == 'super_id' || $allow_key == 'agent_id' || $allow_key == 'parent_id') {
                self::_checkBeyondAdmin($admin_id, $params[$allow_key], "权限检查失败 当前用户:{$admin_id}, 目标客户:{$params[$allow_key]}");
            } elseif ($allow_key == 'room_id') {
                self::_checkBeyondRoom($admin_id, $params[$allow_key], "权限检查失败 当前用户:{$admin_id}, 目标频道:{$params[$allow_key]}");
            } elseif ($allow_key == 'player_id') {
                self::_checkBeyondPlayer($admin_id, $params[$allow_key], "权限检查失败 当前用户:{$admin_id}, 目标播放器:{$params[$allow_key]}");
            } elseif ($allow_key == 'stream_id') {
                self::_checkBeyondStream($admin_id, $params[$allow_key], "权限检查失败 当前用户:{$admin_id}, 目标视频流:{$params[$allow_key]}");
            }
        }

        if (count($params) == 1 && isset($params['super_id'])) {
            if (!self::_isSuper($admin_id)) {
                throw new ApiAuthBeyondError("{$method}<{$line}> 权限检查失败 当前用户:{$admin_id} 必须为超管");
            }
        }
        if (count($params) == 2 && isset($params['super_id']) && isset($params['agent_id'])) {
            if (!self::_isSuper($admin_id) && !self::_isAgent($admin_id)) {
                throw new ApiAuthBeyondError("{$method}<{$line}> 权限检查失败 当前用户:{$admin_id} 必须为代理");
            }
        }
        if (count($params) == 3 && isset($params['super_id']) && isset($params['agent_id']) && isset($params['parent_id'])) {
            if (!self::_isSuper($admin_id) && !self::_isAgent($admin_id) && !self::_isParent($admin_id)) {
                throw new ApiAuthBeyondError("{$method}<{$line}> 权限检查失败 当前用户:{$admin_id} 必须为客户");
            }
        }
    }

    public static function _checkAgentVLimitAllRoom($agent_id, $vlimit_all_room = 0, $auto_ex = '')
    {
        if (!self::_isAgent($agent_id)) {
            if (!empty($auto_ex)) {
                throw new ApiAuthBeyondError($auto_ex);
            }
            return false;
        }
        if ($vlimit_all_room == 0) {
            return true;
        }

        // 检查 代理 对下属客户 最大并发数限制 配置   新开客户 并发总数 不可超过代理 单客户最大并发数
        $vlimit_per_parent = AdminUser::vlimit_per_parent($agent_id);
        if ($vlimit_per_parent == 0) {
            return true;
        }

        if ($vlimit_all_room > $vlimit_per_parent) {
            if (!empty($auto_ex)) {
                throw new ApiCheckLimitError($auto_ex);
            }
            return false;
        }
        return true;
    }

    public static function _checkAgentVLimitPerRoom($agent_id, $viewlimit, $auto_ex = '')
    {
        if (!self::_isAgent($agent_id)) {
            if (!empty($auto_ex)) {
                throw new ApiAuthBeyondError($auto_ex);
            }
            return false;
        }
        // 检查 当个频道并发 是否超出 该代理 单频道 最大人数限制
        $vlimit_per_room = AdminUser::vlimit_per_room($agent_id);
        if ($vlimit_per_room <= 0) {
            return true;
        }
        if ($viewlimit > $vlimit_per_room) {
            if (!empty($auto_ex)) {
                throw new ApiCheckLimitError($auto_ex);
            }
            return false;
        }
        return true;
    }

    public static function _sumParentVLimitAllRoomByAgent($agent_id, $pass_parent_id = 0)
    {
        $pass_parent_id = $pass_parent_id > 0 ? intval($pass_parent_id) : 0;
        if (!self::_isAgent($agent_id)) {
            throw new NeverRunAtHereError("admin type error agent_id:{$agent_id}");
        }

        $sum = AdminUser::_sum(AdminUser::tableBuilder([
            'admin_id' => Q::where($pass_parent_id, '!=', function () use ($pass_parent_id) {
                return $pass_parent_id > 0;
            }),
            'agent_id' => $agent_id,
            'admin_type' => AdminTypeEnum::PARENT_VALUE,
            'state' => Q::whereIn([StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE]),
        ]), 'vlimit_all_room');
        return $sum;
    }

    public static function _sumRoomViewLimitByParent($parent_id, $pass_room_id = 0)
    {
        $pass_room_id = $pass_room_id > 0 ? intval($pass_room_id) : 0;
        if (!self::_isParent($parent_id)) {
            throw new NeverRunAtHereError("admin type error parent_id:{$parent_id}");
        }
        $sum = LiveRoom::_sum(LiveRoom::tableBuilder([
            'room_id' => Q::where($pass_room_id, '!=', function () use ($pass_room_id) {
                return $pass_room_id > 0;
            }),
            'admin_id' => $parent_id,
            'state' => Q::whereIn([StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE]),
        ]), 'viewlimit');
        return $sum;
    }

    public static function _maxCanUseViewLimitByParent($parent_id, $pass_room_id = 0)
    {
        $pass_room_id = $pass_room_id > 0 ? intval($pass_room_id) : 0;
        if (!self::_isParent($parent_id)) {
            throw new NeverRunAtHereError("admin type error parent_id:{$parent_id}");
        }
        $vlimit_all_room = AdminUser::vlimit_all_room($parent_id);
        if ($vlimit_all_room <= 0) {
            // 请先判断 vlimit_all_room 为 不限制的情况  只在限制存在的情况下 在调用此方法计算剩余可用并发数
            throw new NeverRunAtHereError("vlimit_all_room eq 0 should skip this check parent_id:{$parent_id}");
        }
        $sum = self::_sumRoomViewLimitByParent($parent_id, $pass_room_id);
        $this_viewlimit = $pass_room_id > 0 ? LiveRoom::viewlimit($pass_room_id) : 0;
        $all_used = $sum - $this_viewlimit;
        return $all_used >= $vlimit_all_room ? 0 : $vlimit_all_room - $sum;   // 总数已经超出 那么频道 并发数 就认为是剩余的最大值  不可再增加
    }

    public static function _maxCanUseVLimitOnlineNumByAgent($agent_id, $pass_parent_id = 0)
    {
        $pass_parent_id = $pass_parent_id > 0 ? intval($pass_parent_id) : 0;
        if (!self::_isAgent($agent_id)) {
            throw new NeverRunAtHereError("admin type error agent_id:{$agent_id}");
        }
        $vlimit_online_num = AdminUser::vlimit_online_num($agent_id);
        if ($vlimit_online_num <= 0) {
            // 请先判断 vlimit_online_num 为 不限制的情况  只在限制存在的情况下 在调用此方法计算剩余可用并发数
            throw new NeverRunAtHereError("vlimit_online_num eq 0 should skip this check agent_id:{$agent_id}");
        }
        $sum = self::_sumParentVLimitAllRoomByAgent($agent_id, $pass_parent_id);
        $this_vlimit_all_room = $pass_parent_id > 0 ? AdminUser::vlimit_all_room($pass_parent_id) : 0;
        $all_used = $sum - $this_vlimit_all_room;
        return $all_used >= $vlimit_online_num ? 0 : $vlimit_online_num - $sum;   // 总数已经超出 那么客户 并发数 就认为是剩余的最大值  不可再增加
    }

    public static function _checkParentVLimitAllRoomForRoom($parent_id, $viewlimit, $pass_room_id = 0, $auto_ex = '')
    {
        if (!self::_isParent($parent_id)) {
            if (!empty($auto_ex)) {
                throw new ApiAuthBeyondError($auto_ex);
            }
            return false;
        }
        $vlimit_all_room = AdminUser::vlimit_all_room($parent_id);
        if ($vlimit_all_room <= 0) {
            return true;
        }
        $can_use = self::_maxCanUseViewLimitByParent($parent_id, $pass_room_id);
        $this_viewlimit = $pass_room_id > 0 ? LiveRoom::viewlimit($pass_room_id) : 0;

        if ($viewlimit > $this_viewlimit && $can_use < $viewlimit - $this_viewlimit) {
            if (!empty($auto_ex)) {
                throw new ApiCheckLimitError($auto_ex);
            }
            return false;
        }
        return true;
    }

    public static function _checkParentVLimitOnlineNumForParent($agent_id, $viewlimit, $pass_parent_id = 0, $auto_ex = '')
    {
        if (!self::_isAgent($agent_id)) {
            if (!empty($auto_ex)) {
                throw new ApiAuthBeyondError($auto_ex);
            }
            return false;
        }
        $vlimit_online_num = AdminUser::vlimit_online_num($agent_id);
        if ($vlimit_online_num <= 0) {
            return true;
        }
        $can_use = self::_maxCanUseVLimitOnlineNumByAgent($agent_id, $pass_parent_id);
        $this_vlimit_all_room = $pass_parent_id > 0 ? AdminUser::vlimit_all_room($pass_parent_id) : 0;

        if ($viewlimit > $this_vlimit_all_room && $can_use < $viewlimit - $this_vlimit_all_room) {
            if (!empty($auto_ex)) {
                throw new ApiCheckLimitError($auto_ex);
            }
            return false;
        }
        return true;
    }

    public static function _checkParentNLimitCountRoom($parent_id, $auto_ex = '')
    {
        if (!self::_isParent($parent_id)) {
            if (!empty($auto_ex)) {
                throw new ApiAuthBeyondError($auto_ex);
            }

            return false;
        }
        $nlimit_count_room = AdminUser::nlimit_count_room($parent_id);
        if ($nlimit_count_room <= 0) {
            return true;
        }

        // 查找该客户下 所有 正常和冻结的 频道 数量  判断是否还可以增加新的 频道
        $num = LiveRoom::countItem([
            'admin_id' => $parent_id,
            'state' => Q::whereIn([StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE]),
        ]);

        if ($num >= $nlimit_count_room) {
            if (!empty($auto_ex)) {
                throw new ApiCheckLimitError($auto_ex);
            }
            return false;
        }
        return true;
    }

    public static function _checkAgentNLimitCountParent($agent_id, $auto_ex = '')
    {
        if (!self::_isAgent($agent_id)) {
            if (!empty($auto_ex)) {
                throw new ApiAuthBeyondError($auto_ex);
            }
            return false;
        }

        $nlimit_count_parent = AdminUser::nlimit_count_parent($agent_id);

        if ($nlimit_count_parent <= 0) {
            return true;
        }

        // 查找该代理下 所有 正常和冻结的 客户 数量  判断是否还可以增加新的 客户
        $num = AdminUser::countItem([
            'agent_id' => $agent_id,
            'admin_type' => AdminTypeEnum::PARENT_VALUE,
            'state' => Q::whereIn([StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE]),
        ]);

        if ($num >= $nlimit_count_parent) {
            if (!empty($auto_ex)) {
                throw new ApiCheckLimitError($auto_ex);
            }
            return false;
        }
        return true;
    }


    /**
     * 检查 某个 sub_admin 是否属于上个 admin
     * @param int $admin_id
     * @param int $sub_admin_id
     * @param string $auto_ex 是否自动尝试抛出异常  非空则检查失败 自动抛出异常
     * @param bool $allow_self 是否允许 自身 默认为 允许
     * @return bool
     * @throws ApiAuthBeyondError
     * @throws ApiAuthError
     * @throws ApiParamsError
     */
    public static function _checkBeyondAdmin($admin_id, $sub_admin_id, $auto_ex = '', $allow_self = true)
    {
        $admin_id = intval($admin_id);
        $sub_admin_id = intval($sub_admin_id);
        if (!AdminUser::checkOne($admin_id)) {
            if (!empty($auto_ex)) {
                throw new ApiAuthError($auto_ex);
            }
            return false;
        }

        if ($admin_id > 0 && $sub_admin_id > 0 && $admin_id == $sub_admin_id) {
            $ret = $allow_self;
            if (!$ret && !empty($auto_ex)) {
                throw new ApiAuthBeyondError($auto_ex);
            }
            return $ret;
        }

        if (!AdminUser::checkOne($sub_admin_id)) {
            if (!empty($auto_ex)) {
                throw new ApiParamsError("帐号不存在");
            }
            return false;
        }

        if (self::_isSuper($admin_id)) {
            return true;
        } else if (self::_isAgent($admin_id)) {
            if (self::_isParent($sub_admin_id)) {
                $ret = AdminUser::agent_id($sub_admin_id) == $admin_id;
                if (!$ret && !empty($auto_ex)) {
                    throw new ApiAuthBeyondError($auto_ex);
                }
                return $ret;
            } else if (self::_isSub($sub_admin_id)) {
                $parent_id = AdminUser::parent_id($sub_admin_id);
                $ret = AdminUser::agent_id($parent_id) == $admin_id;
                if (!$ret && !empty($auto_ex)) {
                    throw new ApiAuthBeyondError($auto_ex);
                }
                return $ret;
            }
        } else if (self::_isParent($admin_id)) {
            if (self::_isSub($sub_admin_id)) {
                $ret = AdminUser::parent_id($sub_admin_id) == $admin_id;
                if (!$ret && !empty($auto_ex)) {
                    throw new ApiAuthBeyondError($auto_ex);
                }
                return $ret;
            }
        }
        if (!empty($auto_ex)) {
            throw new ApiAuthBeyondError($auto_ex);
        }
        return false;
    }

    /**
     * 检查 是否拥有 管理 siteMgr 权限
     * @param int $admin_id
     * @param int $mgr_id
     * @param string $auto_ex 是否自动尝试抛出异常  非空则检查失败 自动抛出异常
     * @return bool
     * @throws ApiAuthBeyondError
     * @throws ApiAuthError
     * @throws ApiParamsError
     */
    public static function _checkBeyondSiteMgr($admin_id, $mgr_id, $auto_ex = '')
    {
        $admin_id = intval($admin_id);
        $mgr_id = intval($mgr_id);
        if (!AdminUser::checkOne($admin_id)) {
            if (!empty($auto_ex)) {
                throw new ApiAuthError($auto_ex);
            }
            return false;
        }

        if (!SiteMgrUser::checkOne($mgr_id)) {
            if (!empty($auto_ex)) {
                throw new ApiParamsError("管理员不存在");
            }
            return false;
        }

        if (self::_isSuper($admin_id)) {
            return true;
        }

        if (!empty($auto_ex)) {
            throw new ApiAuthBeyondError($auto_ex);
        }
        return false;
    }

    /**
     * 检测 某个 订单 是否属于 某个 admin
     * @param int $admin_id
     * @param int $order_id
     * @param string $auto_ex 是否自动尝试抛出异常  非空则检查失败 自动抛出异常
     * @return bool
     * @throws ApiAuthBeyondError
     * @throws ApiAuthError
     * @throws ApiParamsError
     */
    public static function _checkBeyondOrder($admin_id, $order_id, $auto_ex = '')
    {
        $admin_id = intval($admin_id);
        $order_id = intval($order_id);
        if (!AdminUser::checkOne($admin_id)) {
            if (!empty($auto_ex)) {
                throw new ApiAuthError($auto_ex);
            }
            return false;
        }

        if (!XdyOrder::checkOne($order_id)) {
            if (!empty($auto_ex)) {
                throw new ApiParamsError("订单不存在");
            }
            return false;
        }
        if (self::_isSuper($admin_id)) {
            return true;
        } else {
            if (!self::_isAdminWithBalance($admin_id)) {
                // 没有 余额 的 客户  不会拥有订单
                if (!empty($auto_ex)) {
                    throw new ApiAuthBeyondError($auto_ex);
                }
                return false;
            }
            $order_admin_id = XdyOrder::admin_id($order_id);
            return self::_checkBeyondAdmin($admin_id, $order_admin_id, $auto_ex);
        }
    }

    /**
     * 检测 某个 产品 是否属于 某个 admin
     * @param int $admin_id
     * @param int $product_id
     * @param string $auto_ex 是否自动尝试抛出异常  非空则检查失败 自动抛出异常
     * @return bool
     * @throws ApiAuthBeyondError
     * @throws ApiAuthError
     * @throws ApiParamsError
     */
    public static function _checkBeyondProduct($admin_id, $product_id, $auto_ex = '')
    {
        $admin_id = intval($admin_id);
        $product_id = intval($product_id);
        if (!AdminUser::checkOne($admin_id)) {
            if (!empty($auto_ex)) {
                throw new ApiAuthError($auto_ex);
            }
            return false;
        }

        if (!XdyProduct::checkOne($product_id)) {
            if (!empty($auto_ex)) {
                throw new ApiParamsError("产品不存在");
            }
            return false;
        }
        if (self::_isSuper($admin_id)) {
            return true;
        } else {
            if (!self::_isAdminWithBalance($admin_id)) {
                // 没有 余额 的 客户  不会拥有 产品
                if (!empty($auto_ex)) {
                    throw new ApiAuthBeyondError($auto_ex);
                }
                return false;
            }
            $product_admin_id = XdyProduct::admin_id($product_id);
            return empty($product_admin_id) || self::_checkBeyondAdmin($admin_id, $product_admin_id, $auto_ex);
        }
    }

    /**
     * 检测 某个 频道 是否属于 某个 admin
     * @param int $admin_id
     * @param int $room_id
     * @param string $auto_ex 是否自动尝试抛出异常  非空则检查失败 自动抛出异常
     * @return bool
     * @throws ApiAuthBeyondError
     * @throws ApiAuthError
     * @throws ApiParamsError
     */
    public static function _checkBeyondRoom($admin_id, $room_id, $auto_ex = '')
    {
        $admin_id = intval($admin_id);
        $room_id = intval($room_id);
        if (!AdminUser::checkOne($admin_id)) {
            if (!empty($auto_ex)) {
                throw new ApiAuthError($auto_ex);
            }
            return false;
        }

        if (!LiveRoom::checkOne($room_id)) {
            if (!empty($auto_ex)) {
                throw new ApiParamsError('频道不存在');
            }
            return false;
        }

        $room_admin_id = LiveRoom::admin_id($room_id);
        if (self::_isSub($admin_id)) {
            if (AdminUser::checkOne($admin_id, 'parent_id', $room_admin_id)) {
                $room_set = self::_loadACLByAdmin($admin_id, 'room');
                $ret = in_array($room_id, $room_set);
                if (!$ret && !empty($auto_ex)) {
                    throw new ApiAuthBeyondError($auto_ex);
                }
                return $ret;
            } else {
                if (!empty($auto_ex)) {
                    throw new ApiAuthBeyondError($auto_ex);
                }
                return false;
            }
        }

        return self::_checkBeyondAdmin($admin_id, $room_admin_id, $auto_ex);
    }

    public static function _checkBeyondPlayer($admin_id, $player_id, $auto_ex = '')
    {
        $admin_id = intval($admin_id);
        $player_id = intval($player_id);
        if (!AdminUser::checkOne($admin_id)) {
            if (!empty($auto_ex)) {
                throw new ApiAuthError($auto_ex);
            }
            return false;
        }

        if (!PlayerBase::checkOne($player_id)) {
            if (!empty($auto_ex)) {
                throw new ApiParamsError("播放器不存在");
            }
            return false;
        }
        $player_admin_id = PlayerBase::admin_id($player_id);
        if ($player_admin_id == 0 && (self::_isAgent($admin_id) || self::_isSuper($admin_id))) {
            return true;   // 当为代理时获取默认播放器配置
        }

        if (self::_isSub($admin_id) && AdminUser::checkOne($admin_id, 'parent_id', $player_admin_id)) {
            // TODO 暂时设置 子账号 与 父账号 同等的 播放器权限
            return true;
        }
        return self::_checkBeyondAdmin($admin_id, $player_admin_id, $auto_ex);
    }

    public static function _checkBeyondStream($admin_id, $stream_id, $auto_ex = '')
    {
        $admin_id = intval($admin_id);
        $stream_id = intval($stream_id);
        if (!AdminUser::checkOne($admin_id)) {
            if (!empty($auto_ex)) {
                throw new ApiAuthError($auto_ex);
            }
            return false;
        }

        if (!StreamBase::checkOne($stream_id)) {
            if (!empty($auto_ex)) {
                throw new ApiParamsError('视频流不存在');
            }
            return false;
        }
        $stream_admin_id = StreamBase::admin_id($stream_id);

        if (self::_isSub($admin_id) && AdminUser::checkOne($admin_id, 'parent_id', $stream_admin_id)) {
            // TODO 暂时设置 子账号 与 父账号 同等的 视频流权限
            return true;
        }
        return self::_checkBeyondAdmin($admin_id, $stream_admin_id, $auto_ex);
    }
}