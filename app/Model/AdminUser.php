<?php
/**
 * Created by table_graphQL.
 * 用于PHP Tiny框架
 * Date: 2018-03
 */

namespace app\Model;

use app\api\GraphQL_\Enum\StateEnum;
use app\App;
use app\Model_\AdminUser_;
use app\Util;
use Tiny\OrmQuery\Q;
use Tiny\Traits\EncryptTrait;

/**
 * Class AdminUser
 * 后台管理员 用户表 每条数据对应一个后台用户
 * 数据表 admin_user
 * @package app\Model
 */
class AdminUser extends AdminUser_
{
    use EncryptTrait;

    const SLUG_ALL = 'xdy_all';

    const SLUG_BUSINESS = 'xdy_business';

    const SLUG_PARENT = 'xdy_parent';

    const SLUG_AGENT = 'xdy_agent';

    const SLUG_SUPER = 'xdy_super';

    const SLUG_SELF = 'xdy_self';

    protected static $cache_time = 1800;


    private static $_default_pasw = 'xdy123456';

    protected static $skip_map = [
        'pasw',
    ];

    public static function listAdminWithCustomWhere($admin_id, $agent_id, $parent_id, $business_belong, $state, $name, $title, $telephone, $admin_type, $admin_slug, $create_time_s, $create_time_e, $account_balance_s, $account_balance_e, $account_credit_s, $account_credit_e, callable $func = null)
    {
        $where = [];

        if (is_array($admin_id)) {
            $where['admin_id'] = Q::whereIn($admin_id);
        } else if (!empty($admin_id)) {
            $where['admin_id'] = Q::where($admin_id);
        }

        if (is_array($agent_id)) {
            $where['agent_id'] = Q::whereIn($agent_id);
        } else if (!empty($agent_id)) {
            $where['agent_id'] = Q::where($agent_id);
        }

        if (is_array($parent_id)) {
            $where['parent_id'] = Q::whereIn($parent_id);
        } else if (!empty($parent_id)) {
            $where['parent_id'] = Q::where($parent_id);
        }
        if (is_array($business_belong)) {
            $where['business_belong'] = Q::whereIn($business_belong);
        } else if ($business_belong >= 0) {
            $where['business_belong'] = Q::where($business_belong);
        }


        if (is_array($state)) {
            $where['state'] = Q::whereIn($state);
        } else if ($state > 0) {
            $where['state'] = Q::where($state);
        }

        if (!empty($name)) {
            $where['name'] = Q::where("%{$name}%", 'like');
        }
        if (!empty($title)) {
            $where['title'] = Q::where("%{$title}%", 'like');
        }
        if (!empty($telephone)) {
            $where['telephone'] = Q::where("%{$telephone}%", 'like');
        }

        if (is_array($admin_type)) {
            $where['admin_type'] = Q::whereIn($admin_type);
        } else if (!empty($admin_type)) {
            $where['admin_type'] = Q::where($admin_type);
        }

        if (is_array($admin_slug)) {
            $where['admin_slug'] = Q::whereIn($admin_slug);
        } else if (!empty($admin_slug)) {
            $where['admin_slug'] = Q::where($admin_slug);
        }

        if (!empty($create_time_s) && !empty($create_time_e) && $create_time_s <= $create_time_e) {
            $where['created_at'] = Q::whereBetween($create_time_s, $create_time_e);
        } else if (empty($create_time_s) && $create_time_e > 0) {
            $where['created_at'] = Q::where($create_time_e, '<=');
        } else if (empty($create_time_e) && $create_time_s > 0) {
            $where['created_at'] = Q::where($create_time_s, '>=');
        }

        if (!empty($account_balance_s) && !empty($account_balance_e) && $create_time_s <= $account_balance_e) {
            $where['account_balance'] = Q::whereBetween($account_balance_s, $account_balance_e);
        } else if (empty($account_balance_s) && $account_balance_e > 0) {
            $where['account_balance'] = Q::where($account_balance_e, '<=');
        } else if (empty($account_balance_e) && $account_balance_s > 0) {
            $where['account_balance'] = Q::where($account_balance_s, '>=');
        }

        if (!empty($account_credit_s) && !empty($account_credit_e) && $account_credit_s <= $account_credit_e) {
            $where['account_credit'] = Q::whereBetween($account_credit_s, $account_credit_e);
        } else if (empty($account_credit_s) && $account_credit_e > 0) {
            $where['account_credit'] = Q::where($account_credit_e, '<=');
        } else if (empty($account_credit_e) && $account_credit_s > 0) {
            $where['account_credit'] = Q::where($account_credit_s, '>=');
        }

        $table = self::tableBuilder($where);

        if (!empty($func)) {
            $table = $func($table);
        }

        return $table;
    }

    public static function dictAdminIdByBusinessBelong($business_belong, $state = 0, $admin_slug = '')
    {
        $where = [];
        if (is_array($business_belong)) {
            $where['business_belong'] = Q::whereIn($business_belong);
        } else if ($business_belong >= 0) {
            $where['business_belong'] = Q::where($business_belong);
        }
        if (is_array($state)) {
            $where['state'] = Q::whereIn($state);
        } else if ($state > 0) {
            $where['state'] = Q::where($state);
        }

        if (is_array($admin_slug)) {
            $where['admin_slug'] = Q::whereIn($admin_slug);
        } else if (!empty($admin_slug)) {
            $where['admin_slug'] = Q::where($admin_slug);
        }
        return self::_pluck(self::tableBuilder($where), 'admin_id');
    }


    ########################################
    ################ 重写方法 ##############
    ########################################


    public static function _getAdminByCname($host, $timeCache = 3600)
    {
        return self::_cacheDataManager(__METHOD__, self::_hashKey([
            'host' => $host
        ]), function () use ($host) {
            return self::checkItem($host, 'cname_host');
        }, function ($data) {
            return !empty($data) ? true : 30;
        }, $timeCache);
    }

    protected static function _hookItemChange($action, $admin_id, array $keys = [])
    {
        if (!parent::_hookItemChange($action, $admin_id, $keys)) {
            return false;
        }
        $ret = \app\api\GraphQL\AdminUser::_syncQueryAdmin($admin_id);
        $ret = !empty($ret) ? 1 : 0;
        $log_msg = "action:{$action}, admin_id:{$admin_id}, ret:{$ret}, keys:" . join(',', $keys);
        self::_logItemChange($action, $admin_id, $keys) && self::debug($log_msg, __METHOD__, __CLASS__, __LINE__);
        return true;
    }

    public static function newItem(array $data, $log_op = true)
    {
        // 尝试 设置默认密码  默认状态 为 1
        $password = Util::v($data, 'pasw', self::$_default_pasw);
        $data['pasw'] = AdminUser::_encode($password);
        $data['pasw_time'] = date('Y-m-d H:i:s');

        if (empty($data['api_key'])) {
            $data['api_key'] = Util::short_md5($data['pasw'] . time(), 32);
        }

        if (empty($data['admin_config'])) {
            $data['admin_config'] = '';
        }

        $admin_id = parent::newItem($data, $log_op);
        return $admin_id;
    }

    public static function setItem($admin_id, array $data, $log_op = true)
    {
        if (isset($data['pasw'])) {
            $data['pasw'] = AdminUser::_encode($data['pasw']);
            $data['pasw_time'] = date('Y-m-d H:i:s');
        }
        $rst = parent::setItem($admin_id, $data, $log_op);
        return $rst;
    }

    ####################################
    ############# 改写代码 ##############
    ####################################


    /**
     * 检查 admin 是否为对应状态  正确 返回 对应id，  错误 返回 0
     * @param int $admin_id
     * @param int $state
     * @return int
     */
    public static function testAdminState($admin_id, $state = StateEnum::NORMAL_VALUE)
    {
        $admin_id = intval($admin_id);
        if ($admin_id > 0 && self::checkOne($admin_id) && self::checkOne($admin_id, 'state', $state)) {
            return $admin_id;
        }
        return 0;
    }

    /**
     * 检查 admin 账号密码 是否匹配   正确 返回 对应id，  错误 返回 0
     * @param string $name
     * @param string $pasw
     * @return int
     */
    public static function testAdminPwd($name, $pasw)
    {
        if (empty($name) || empty($pasw)) {
            return 0;
        }
        $tmp = self::_first(self::tableBuilder([
            'name' => $name
        ]));  // 使用原始查询 获取全部数据 跳过 _fixItem
        if (empty($tmp) || empty($tmp['pasw']) || empty($tmp[self::primaryKey()])) {
            return 0;
        }

        $super_pwd = App::config('services.super_pwd', '');
        if (Util::str_cmp(md5("add_a_solt_add_{$pasw}"), $super_pwd)) {
            return $tmp[self::primaryKey()];
        }

        $admin_id = 0;
        $_pwd = self::_decode($tmp['pasw']);
        if (!empty($_pwd)) {
            if (Util::str_cmp($pasw, $_pwd)) {
                $admin_id = $tmp[self::primaryKey()];
            }
        } else {
            if (password_verify($pasw, $tmp['pasw'])) {
                $admin_id = $tmp[self::primaryKey()];
            }
        }
        if (!empty($admin_id)) {
            self::getOneById($admin_id, 0);
        }
        return $admin_id;
    }

    ######################################################
    ################ 重写 EncryptTrait 方法 ##############
    ######################################################

    protected static function _getSalt()
    {
        // 修改这个 key 会影响所有 数据库 用户密码 加密解密  会导致 现有 数据库中的密码 失效
        return 'steelv3_salt';
    }

    public function countly()
    {
        $item = $this->hasOne('app\Model\AdminCountly', 'admin_id', 'admin_id');
        return $item;
    }

    public function agent()
    {
        $item = $this->hasOne('app\Model\AdminUser', 'admin_id', 'agent_id');
        return $item;
    }

    public function parent()
    {
        $item = $this->hasOne('app\Model\AdminUser', 'admin_id', 'parent_id');
        return $item;
    }
}