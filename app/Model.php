<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/10/25
 * Time: 9:42
 */

namespace app;

use app\Model\SiteOpRecord;
use Tiny\Model as _Model;
use Tiny\Traits\OrmConfig;

class Model extends _Model
{
    protected static $cache_time = 0;
    protected static $max_select = 100000;
    protected static $orm_debug = false;

    public $timestamps = false;

    protected static $_redis_prefix_db = "RDB";

    const ENUM_OP_TYPE_INSERT = 1;
    const  ENUM_OP_TYPE_UPDATE = 2;
    const ENUM_OP_TYPE_DELETE = 3;

    protected static function _logItemChange($action, $id, array $keys = [])
    {
        false && func_get_args();
        return App::dev();
    }

    protected static function _hookItemChange($action, $id, array $keys = [])
    {
        if (App::config('app.dev_skip_sync')) {
            return false;
        }
        return parent::_hookItemChange($action, $id, $keys);
    }

    final protected static function tryFixOrderOption($orderOption)
    {
        $orderOption[0] = !empty($orderOption[0]) ? trim($orderOption[0]) : self::primaryKey();
        $orderOption[1] = !empty($orderOption[1]) ? trim(strtolower($orderOption[1])) : 'desc';
        $orderOption[1] = $orderOption[1] == 'asc' ? 'asc' : 'desc';
        return [$orderOption[0], $orderOption[1]];
    }

    /**
     * 使用这个特性的子类必须 实现这个方法 返回特定格式的数组 表示数据表的配置
     * @return OrmConfig
     */
    final protected static function getOrmConfig()
    {
        return static::_getInstanceByKey(get_called_class() . "_OrmConfig_object", function () {
            $tmp = self::_createStaticInstance();
            $table_name = $tmp->getTable();
            $_primary_key = $tmp->getKeyName();
            $primary_key = !empty($_primary_key) ? $_primary_key : 'id';

            $db_config = App::config('ENV_DB');
            $db_name = !empty($db_config['database']) ? $db_config['database'] : 'test';
            return new OrmConfig($db_name, $table_name, $primary_key, static::$cache_time, static::$max_select, static::$orm_debug);
        });
    }

    protected static function getCachePreFix()
    {
        return 'SDB';
    }

    protected static function _fixItem($val)
    {
        $_hiddenFields = static::_hiddenFields();
        foreach ($_hiddenFields as $hiddenField) {
            unset($val[$hiddenField]);
        }
        return $val;
    }

    public static function getBuilder()
    {
        return self::_createStaticInstance()->newQuery();
    }

    /**
     * @return static
     */
    final protected static function _createStaticInstance()
    {
        return static::_getInstanceByKey(get_called_class() . "_static", function () {
            return new static();
        });
    }

    protected $sortable = [];
    protected $allfields = [];

    public function getAllFields()
    {
        return $this->allfields;
    }

    public function getSortAble()
    {
        return $this->sortable;
    }

    /**
     * 检查字段是否可以填充  true  可填充  false  不可填充
     * @param string $key
     * @return bool
     */
    final protected static function _fillAble($key)
    {
        $_key = Util::trimlower($key);
        $_fillableMap = static::_getInstanceByKey(get_called_class() . "_fillAble_array", function () {
            $tmp = self::_createStaticInstance();
            $fillable = $tmp->getAllFields();
            return Util::build_map($fillable, true, 1);
        });
        $_key = Util::trimlower($_key);
        return !empty($_fillableMap[$_key]);
    }

    /**
     * 返回需要隐藏的 字段 列表
     * @return array
     */
    final protected static function _hiddenFields()
    {
        $_hiddenFields = static::_getInstanceByKey(get_called_class() . "_hiddenFields_array", function () {
            $tmp = self::_createStaticInstance();
            return $tmp->getHidden();
        });
        return $_hiddenFields;
    }


    #########################################################
    ####################### 自动数据表记录 ####################
    #########################################################

    public static $_op_record_map = [
        'stream_base' => [
            'cls' => '\\app\\Model\\StreamBase',
            'name' => '基础视频流',
            'skip' => ['live_state']
        ],
        'admin_access_control' => [
            'cls' => '\\app\\Model\\AdminAccessControl',
            'name' => '权限控制',
        ],
        'admin_user' => [
            'cls' => '\\app\\Model\\AdminUser',
            'name' => '后台账号',
            'skip' => ['agent_num', 'parent_num', 'sub_num', 'room_num', 'player_num', 'stream_num', 'viewer_now', 'viewer_max', 'viewer_max_at']
        ],
        'stream_mcs' => [
            'cls' => '\\app\\Model\\StreamMcs',
            'name' => '直播视频流',
        ],
        'live_room' => [
            'cls' => '\\app\\Model\\LiveRoom',
            'name' => '频道',
            'skip' => ['viewer_now', 'viewer_count', 'viewer_max', 'viewer_max_at', 'live_state']
        ],
        'admin_countly' => [
            'cls' => '\\app\\Model\\AdminCountly',
            'name' => 'Countly实例',
        ],
        'xdy_admin_product' => [
            'cls' => '\\app\\Model\\XdyAdminProduct',
            'name' => '客户购买产品',
        ],
        'xdy_product' => [
            'cls' => '\\app\\Model\\XdyProduct',
            'name' => '产品类型',
        ],
        'room_content_config' => [
            'cls' => '\\app\\Model\\RoomContentConfig',
            'name' => 'Content配置',
        ],
        'article_list' => [
            'cls' => '\\app\\Model\\ArticleList',
            'name' => '文章列表',
        ],
        'help_doc_list' => [
            'cls' => '\\app\\Model\\HelpDocList',
            'name' => '帮助中心',
        ],
        'site_mgr_user' => [
            'cls' => '\\app\\Model\\SiteMgrUser',
            'name' => '网站管理员',
        ],
        'article_classify' => [
            'cls' => '\\app\\Model\\ArticleClassify',
            'name' => '文章分类',
        ],
        'stream_pull' => [
            'cls' => '\\app\\Model\\StreamPull',
            'name' => '拉流视频流',
        ],
        'player_mps' => [
            'cls' => '\\app\\Model\\PlayerMps',
            'name' => 'MPS播放器',
        ],
        'player_base' => [
            'cls' => '\\app\\Model\\PlayerBase',
            'name' => '基础播放器',
        ],
        'player_aodian' => [
            'cls' => '\\app\\Model\\PlayerAodian',
            'name' => '奥点播放器',
        ],
        'player_ali' => [
            'cls' => '\\app\\Model\\PlayerAli',
            'name' => '阿里播放器',
        ],

    ];

    public static function newItem(array $data, $log_op = true)
    {
        if ($log_op) {
            $op_type = self::ENUM_OP_TYPE_INSERT;
            $ret = parent::newItem($data, $log_op);
            $last_value = self::_loadLastValueById($ret);
            self::_saveOpRecord($op_type, $ret, $data, $last_value, $last_value);
            return $ret;
        } else {
            return parent::newItem($data, $log_op);
        }
    }

    public static function setItem($id, array $data, $log_op = true)
    {
        if ($log_op) {
            $op_table = static::tableName();
            $pass_keys = Util::v(Util::v(self::$_op_record_map, $op_table, []), 'skip', []);
            $test = Util::build_map(array_keys($data), true, 1, $pass_keys);

            if (empty($test)) {
                return parent::setItem($id, $data, $log_op);
            }

            $op_type = self::ENUM_OP_TYPE_UPDATE;
            $last_value = self::_loadLastValueById($id);
            $ret = parent::setItem($id, $data, $log_op);
            $this_value = self::_loadLastValueById($id);
            if (!self::_diffArrayItem($last_value, $this_value)) {
                self::_saveOpRecord($op_type, $id, $data, $last_value, $this_value);
            }
            return $ret;
        } else {
            return parent::setItem($id, $data, $log_op);
        }
    }

    public static function delItem($id, $log_op = true)
    {
        if ($log_op) {
            $op_type = self::ENUM_OP_TYPE_DELETE;
            $last_value = self::_loadLastValueById($id);
            $ret = parent::delItem($id, $log_op);
            self::_saveOpRecord($op_type, $id, [], $last_value, $last_value);
            return $ret;
        } else {
            return parent::delItem($id, $log_op);
        }
    }

    /**
     * @param $id
     * @return array|mixed|static
     */
    private static function _loadLastValueById($id)
    {
        $op_table = static::tableName();
        if (!isset(self::$_op_record_map[$op_table])) {
            return [];  // 不重要的数据表  不在存取 备份记录
        }

        $last_value = static::_first(static::tableBuilder([
            static::primaryKey() => $id
        ]));
        $last_value = Util::try2array($last_value);
        unset($last_value['created_at'], $last_value['updated_at']);

        $tableCfg = Util::v(self::$_op_record_map, $op_table, []);
        $passKeys = Util::v($tableCfg, 'skip', []);
        foreach ($passKeys as $pass_key) {
            unset($last_value[$pass_key]);
        }
        return $last_value;
    }

    private static function _diffArrayItem($item1, $item2)
    {
        return json_encode($item1) == json_encode($item2);
    }

    private static function _saveOpRecord($op_type, $op_prival, $op_args = [], $last_value = [], $this_value = [])
    {
        if ($op_type != 1 && $op_type != 2 && $op_type != 3) {
            return;
        }
        $last_value = !empty($last_value) ? $last_value : [];
        $this_value = !empty($this_value) ? $this_value : [];

        $op_table = static::tableName();
        if (!isset(self::$_op_record_map[$op_table])) {
            return;  // 不重要的数据表  不在存取 备份记录
        }

        $op_args = self::_fixFillAbleData($op_args);  // 过滤掉 无用参数
        if ($op_type == 2 && empty($op_args)) {
            return;
        }
        $ctrl = Controller::_getRequestByCtx();
        $ip = !empty($ctrl) ? $ctrl->client_ip() : '0.0.0.0';
        $op_uri = !empty($ctrl) ? $ctrl->full() : '';
        $op_refer = !empty($ctrl) ? $ctrl->getHttpReferer() : '';

        $data = [
            'op_type' => $op_type,   //   操作类型  0 未知  1 插入  2 更改 3 删除
            'op_table' => $op_table,
            'op_prikey' => static::primaryKey(),
            'op_uid' => Controller::_getAuthByCtx() ? Controller::_getAuthByCtx()->id() : 0,
            'op_prival' => $op_prival,
            'op_args' => $op_args,
            'op_diff' => self::_diffItemValue($last_value, $this_value),
            'op_ip' => $ip,
            'op_location' => Util::getIpLocation($ip),
            'op_uri' => strlen($op_uri) > 255 ? substr($op_uri, 0, 255) : $op_uri,
            'op_refer' => strlen($op_refer) > 255 ? substr($op_refer, 0, 255) : '',
            'last_value' => $last_value,
            'this_value' => $this_value,
        ];
        SiteOpRecord::createOne($data);
    }

    private static function _diffItemValue($last_value, $this_value)
    {
        if (empty($last_value) || empty($this_value)) {
            return [];
        }
        $diff = [];
        foreach ($this_value as $key => $val) {
            if (!empty($key)) {
                if (!isset($last_value[$key]) || $val != $last_value[$key]) {
                    $diff[$key] = $val;
                }
            }
        }
        return $diff;
    }

}