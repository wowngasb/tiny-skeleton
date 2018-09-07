<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/8/14
 * Time: 12:03
 */

namespace app\api;

use app\AdminController;
use app\api\Abstracts\AbstractApi;
use app\api\GraphQL\AdminUser;
use app\api\GraphQL_\Enum\AdminTypeEnum;
use app\api\GraphQL_\Enum\StreamTypeEnum;
use app\App;
use app\Exception\ApiParamsError;
use app\Libs\AdminAuth;
use app\Libs\IpAddrHelper;
use app\Model\AdminUser as _AdminUser;
use app\Model\StreamBase;
use app\Model\StreamMcs;
use app\Util;

class ApiHub extends AbstractApi
{

    ################################################################
    ###########################  beforeAction ##########################
    ################################################################

    protected static $detail_log = true;

    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);
        if (isset($params['name'])) {
            $params['name'] = trim(strval($params['name']));
        }

        if (Util::stri_cmp('testSum', $this->_getActionName())) {
            $params['a'] = intval($params['a']);
            $params['b'] = intval($params['b']);
        }

        if (isset($params['id'])) {
            $params['id'] = intval($params['id']);
        }

        if (isset($params['page'])) {
            $params['page'] = $params['page'] > 1 ? intval($params['page']) : 1;
        }
        if (isset($params['num'])) {
            $params['num'] = $params['num'] > 1 ? intval($params['num']) : 20;
        }

        return $params;
    }

    ################################################################
    ###########################  Auto build ##########################
    ################################################################

    /**
     * 自动编译  用于 webpack
     * @return array
     */
    public function autoBuildComponentMap()
    {
        $router_dir = App::path(['resources', 'views', 'dashboard', 'src', 'router'], false);
        $view_dir = App::path(['resources', 'views', 'dashboard', 'src', 'views'], false);
        $fragments_dir = App::path(['resources', 'views', 'dashboard', 'src'], false);

        $component_map = [];
        foreach (AdminTypeEnum::ALL_ENUM_VALUE as $tag) {
            list($route, $acl) = AdminController::_buildMenuByTag($tag);
            false && !empty($acl);
            foreach ($route as $menu) {
                $component_map = self::collectComponent($menu, $component_map);
            }
        }
        foreach ($component_map as $file => $val) {
            if ($file != 'Main' && !Util::stri_endwith($file, 'home.vue')) {
                self::tryCreateFile($view_dir, $file);
            }
        }
        self::dumpImportMap($router_dir, $component_map);
        $fragmentsMap = GraphQLApi::_getFragmentsMap();
        self::dumpGraphQLFragments($fragments_dir, 'fragments.js', $fragmentsMap);
        self::dumpGraphQLUnionTypes($fragments_dir, 'fragmentTypes.json');
        return $component_map;
    }

    /**
     * 自动编译  GraphQLTypes
     * @param string $base_dir
     * @param string $file
     * @param bool $overwrite
     */
    private static function dumpGraphQLUnionTypes($base_dir, $file, $overwrite = true)
    {
        $query = <<<EOT
{
    __schema {
      types {
        kind
        name
        possibleTypes {
          name
        }
      }
    }
}
EOT;

        $api = GraphQLApi::createGraphQLApi();

        $result = $api->exec($query);
        if (is_object($result) && is_callable([$result, 'toArray'])) {
            $result = call_user_func_array([$result, 'toArray'], []);
        }

        $js_str = json_encode($result['data']);

        $f = "{$base_dir}/{$file}";
        if (!is_file($f) || $overwrite) {
            if (is_file($f)) {
                $tmp = file_get_contents($f);
                if (md5(trim($tmp)) == md5(trim($js_str))) {
                    return;
                }
            }
            file_put_contents($f, $js_str);
        }
    }

    /**
     * 自动编译 组件 map 用于 vue-router
     * @param string $base_dir
     * @param array $component_map
     * @param bool $overwrite
     * @param string $file_name
     */
    private static function dumpImportMap($base_dir, $component_map, $overwrite = true, $file_name = 'map.js')
    {
        $js_str = <<<EOT
export default {

EOT;
        foreach ($component_map as $file => $val) {
            if (Util::stri_endwith($file, 'home.vue')) {
                $_file = '@/views/home/home.vue';
            } else {
                $_file = "@/views/{$file}";
            }
            if ($file != 'Main') {
                $js_str .= "    '{$file}': () => import('{$_file}'),\n";
            }
        }

        $js_str .= <<<EOT
}
EOT;

        $f = "{$base_dir}/{$file_name}";
        if (!is_file($f) || $overwrite) {
            if (is_file($f)) {
                $tmp = file_get_contents($f);
                if (md5(trim($tmp)) == md5(trim($js_str))) {
                    return;
                }
            }
            file_put_contents($f, $js_str);
        }
    }

    /**
     * 尝试 创建 默认 vue 文件
     * @param string $base_dir
     * @param string $file
     * @param bool $overwrite
     */
    private static function tryCreateFile($base_dir, $file, $overwrite = false)
    {
        $dir_name = dirname("{$base_dir}/{$file}");
        $file_tag = str_replace('/', '-', $file);
        $file_tag = str_replace('.vue', '', $file_tag);
        Util::mkdir_r($dir_name);

        $js_str = <<<EOT
import util from "@/libs/util.js";
import * as types from "@/types";

export default {
  components: {
  },
  data() {
    return {
    };
  },
  computed: {
  },
  methods: {
    init() {
    }
  },
  watch: {
  },
  mounted() {
    this.init();
  },
  created() {
  }
};
EOT;

        $file_str = <<<EOT
<style lang="less">
</style>

<template>
  <div class=" {$file_tag}">
  </div>
</template>
<script>
{$js_str}
</script>
EOT;

        $f = "{$base_dir}/{$file}";
        if (!is_file($f) || $overwrite) {
            file_put_contents($f, $file_str);
        }

    }

    /**
     * 从菜单构建组件 map
     * @param array $menu
     * @param array $last
     * @return array
     */
    private static function collectComponent(array $menu, array $last)
    {
        $component = Util::v($menu, 'component', '');
        if (!empty($component)) {
            $last[$component] = 1;
        }
        $children = Util::v($menu, 'children', []);
        if (!empty($children)) {
            foreach ($children as $child) {
                $last = self::collectComponent($child, $last);
            }
        }
        return $last;
    }

    /**
     * 构建 GraphQLFragments
     * @param string $base_dir
     * @param string $file
     * @param array $fragmentsMap
     * @param bool $overwrite
     */
    private static function dumpGraphQLFragments($base_dir, $file, array $fragmentsMap, $overwrite = true)
    {
        $js_str = "";
        foreach ($fragmentsMap as $key => $item) {
            $q_str = !empty($item[0]) ? $item[0] : '';
            $q_arr = !empty($item[1]) ? $item[1] : [];
            if (empty($q_str)) {
                continue;
            }
            $js_str .= "\nexport const {$key} = `\n{$q_str}";

            $js_str .= "\n";
            foreach ($q_arr as $dep) {
                $js_str .= '${' . $dep . '}' . "\n";
            }
            $js_str .= "`;\n";
        }

        $f = "{$base_dir}/{$file}";
        if (!is_file($f) || $overwrite) {
            if (is_file($f)) {
                $tmp = file_get_contents($f);
                if (md5(trim($tmp)) == md5(trim($js_str))) {
                    return;
                }
            }
            file_put_contents($f, $js_str);
        }
    }

    ################################################################
    ###########################  测试 API ##########################
    ################################################################

    public function iAppsCreate()
    {
        $method = __METHOD__;
        $method = Util::method2name($method);
        $api = Util::humpToLine($method, '/');
        return [
            'api' => $api
        ];
    }

    public function testToken($id, $expiry = 36000)
    {
        $develop_key = App::config('ENV_DEVELOP_KEY', '');

        $pwd = _AdminUser::_encode('ws123456', 0);

        return [
            'pwd' => $pwd,
            'token' => AdminAuth::_encode("{$id}", $expiry),
            'dev' => App::encrypt($develop_key),
        ];
    }

    public function testMd5($str = "123456", $len = 8)
    {
        return [
            'str' => $str,
            'len' => $len,
            'short_md5' => Util::short_md5($str, $len)
        ];
    }

    public function testHash($str = "123456", $len = 8)
    {
        return [
            'str' => $str,
            'len' => $len,
            'short_hash' => Util::short_hash($str, $len)
        ];
    }

    public function testGQL()
    {
        return [
            'q' => AdminUser::_getSyncQuery(),
        ];
    }

    public function testSubscribe($topic = 'live_notify', $notifyUrl = 'test-red.wenshunsoft.com/helper/push.php', $query = 'a=1&b=2')
    {
        false && func_get_args();

        return StreamMgr::crontabAddDmsLiveNotifyUrl();
    }

    public function tryFixMsgInfo($stream_id)
    {
        $stream_id = intval($stream_id);
        if (StreamBase::stream_type($stream_id) != StreamTypeEnum::STREAM_MCS_VALUE) {
            throw new ApiParamsError("参数错误");
        }
        $test = StreamMcs::getOneById($stream_id);
        if (!empty($test)) {
            throw new ApiParamsError("已修复");
        }

        $account = Util::rand_str(8);
        $password = '123456';
        $stream = Util::rand_str(10);
        $vhost = '';
        $rst = Util::wsOpenApi()->addMcs($account, $password, $stream, $vhost);
        if (!empty($rst['code']) && $rst['code'] == 100) {
            $mcs_id = $rst['data']['id'];
            StreamMcs::createOne([
                'stream_id' => $stream_id,
                'mcs_id' => $mcs_id,
                'mcs_config' => json_encode($rst['data']),
            ]);
        }
        return $rst;
    }


    public function testConfig()
    {
        return [
            'services.openapi.srv' => App::config('services.openapi.srv'),
            'services.openapi.key' => App::config('services.openapi.key'),
        ];
    }

    public function testInfoMcs($mcs_id)
    {
        return Util::wsOpenApi()->infoMcs($mcs_id);
    }

    public function testListHost()
    {
        return Util::wsOpenApi()->listHost();
    }

    public function testDelMcs($mcs_id)
    {
        return Util::wsOpenApi()->delMcs($mcs_id);
    }

    public function testAddMcs($account, $password, $stream, $vhost)
    {
        return Util::wsOpenApi()->addMcs($account, $password, $stream, $vhost);
    }

    public function testInfoAccount($account)
    {
        return Util::wsOpenApi()->infoAccount($account);
    }

    public function testEditMcs($mcs_id, $password)
    {
        return Util::wsOpenApi()->editMcs($mcs_id, [
            'password' => md5($password),
            'password_str' => $password,
            'state' => 0,
        ]);
    }

    public function testLoad($s_id)
    {
        $idx = strpos($s_id, '_', 1);
        list($agent_rand, $user_id) = $idx > 0 ? [substr($s_id, 0, $idx), substr($s_id, $idx + 1)] : ['', ''];
        if (empty($agent_rand) || empty($user_id)) {
            return ['result' => 'Error', 'msg' => 'error args'];
        }
        $ipInfo = IpAddrHelper::loadClientIp($agent_rand, $user_id);
        return ['info' => $ipInfo];
    }

    public function encrypt($str)
    {
        return ['token' => App::encrypt($str)];
    }

    public function decrypt($token)
    {
        return ['str' => App::decrypt($token)];
    }

    /**
     * api hello
     * @param string $name
     * @return array
     */
    public function hello($name = 'world')
    {
        $msg = "test log name={$name}";
        self::debug($msg, __METHOD__, __CLASS__, __LINE__);
        self::info($msg, __METHOD__, __CLASS__, __LINE__);
        self::warn($msg, __METHOD__, __CLASS__, __LINE__);
        self::error($msg, __METHOD__, __CLASS__, __LINE__);
        self::fatal($msg, __METHOD__, __CLASS__, __LINE__);

        return ['info' => "Hello, {$name}!"];
    }

    public function testError($id)
    {
        if ($id <= 0) {
            throw new ApiParamsError('id must gt 0');
        }
        return ['id' => $id, 'info' => 'some info'];
    }

    /**
     * test sum
     * @param int $a
     * @param int $b
     * @return array
     */
    public function testSum($a, $b)
    {
        $sum = $a + $b;
        $msg = "test log a={$a} b={$b}, sum={$sum}";
        self::debug($msg, __METHOD__, __CLASS__, __LINE__);
        self::info($msg, __METHOD__, __CLASS__, __LINE__);
        self::warn($msg, __METHOD__, __CLASS__, __LINE__);
        self::error($msg, __METHOD__, __CLASS__, __LINE__);
        self::fatal($msg, __METHOD__, __CLASS__, __LINE__);
        return ['data' => $sum];
    }

    public function testQuery($page = 0, $num = 20, array $sort_option = ['room_id', 'asc'], $room_id = 0, array $room_id_list = [], $room_title = '')
    {
        $skip = ($page - 1) * $num;

        $total = $skip;
        $list = [];
        $rst = ['list' => $list, 'total' => $total];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

}