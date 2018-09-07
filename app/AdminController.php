<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/1/24 0024
 * Time: 10:06
 */

namespace app;


use app\api\Abstracts\Api;
use app\api\AuthMgr;
use app\api\GraphQL_\Enum\AdminTypeEnum;
use app\Model\AdminUser;
use app\Model\LiveRoom;
use app\Model\PlayerBase;
use app\Model\StreamBase;

class AdminController extends Controller
{

    protected static function _D($data, $tags = null, $ignoreTraceCalls = 0)
    {
        $request = Controller::_getRequestByCtx();
        if (!empty($request)) {
            $tags = $request->debugTag($tags);
        }
        App::_D($data, $tags, $ignoreTraceCalls);
    }

    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);
        $this->assign('title', '管理后台');

        if ($this->auth()->guest()) {
            $this->auth()->logout();
            if ($this->getRequest()->ajax()) {
                return $this->response()->json(['code' => 401, 'msg' => '未登陆']);
            } else {
                return $this->redirect()->guest('auth');
            }
        } else {
            $admin_id = $this->auth()->id();
            if (!AdminUser::testAdminState($admin_id)) {
                $this->auth()->logout();
                return $this->redirect()->guest('auth');
            }

            $agentBrowser = $this->getRequest()->agent_browser();
            if (Util::stri_cmp($agentBrowser[0], 'IE') && intval($agentBrowser[1]) <= 8) {
                return $this->redirect('/badbrowser/alert.html');
            }

            list($appRouter, $appRouterACL, $appRouterAllACL) = self::_buildAppRouter($admin_id);
            $codeHost = Api::_getCnameByAdmin($admin_id, $this->getRequest()->host());
            $this->assign('codeHost', $codeHost);

            $adminConfig = Api::_getAdminConfigByAdmin($admin_id);
            $this->assign('adminConfig', $adminConfig);

            $this->assign('appRouter', $appRouter);
            $this->assign('appRouterACL', $appRouterACL);
            $this->assign('appRouterAllACL', $appRouterAllACL);

            $dmsConfig = self::_buildDmsInfo($admin_id, $agentBrowser);
            $this->assign('dmsConfig', $dmsConfig);
        }

        $firstBaseAgentId = AuthMgr::_tryFindFirstBaseAgentId();
        $this->assign('firstBaseAgentId', $firstBaseAgentId);

        return $params;
    }

    private static function _buildDmsInfo($admin_id, $agentBrowser)
    {
        $time = time();
        $admin_type = AdminUser::admin_type($admin_id);
        $rand = rand(100, 999);
        $countly_pre = App::config('ENV_WEB.countly_pre', 'steel');
        $clientId = "{$countly_pre}|{$time}_{$admin_type}{$agentBrowser[0]}{$rand}_$admin_id";
        $pubKey = Util::short_md5($clientId . uniqid(), 24);
        $tSeq = intval(time() / 10);
        $apiKey = App::config('services.black.key', '');
        $subKey = md5("{$apiKey}_{$clientId}_{$pubKey}_{$tSeq}");
        $topicList = [
            self::_selfAdminTopic($admin_id),
        ];
        // 广播话题  admin  相关信息改动 只会向上级管理员 广播
        if (Api::_isSuper($admin_id)) {
            $topicList[] = self::_superAllTopic();
        } elseif (Api::_isAgent($admin_id)) {
            $topicList[] = self::_agentAllTopic($admin_id);
        } elseif (Api::_isParent($admin_id)) {
            $topicList[] = self::_parentAllTopic($admin_id);
        } elseif (Api::_isSub($admin_id)) {
            $topicList[] = self::_subAllTopic($admin_id);
            $parent_id = AdminUser::parent_id($admin_id);
            $topicList[] = self::_parentAllTopic($parent_id);
        }
        $outTopicList = [];
        foreach (['admin', 'room', 'stream', 'player'] as $tag) {
            $outTopicList = array_merge($outTopicList, self::_autoAddTopicHash($topicList, $tag));
        }
        return [
            'subKey' => $subKey,
            'pubKey' => $pubKey,
            'clientId' => $clientId,
            'topicList' => $outTopicList,
        ];
    }

    public static function _listSyncTopicForPlayerInfo($player_id)
    {
        $admin_id = PlayerBase::admin_id($player_id);
        $topicList = self::_listSyncTopicByAdminId($admin_id);
        return self::_autoAddTopicHash($topicList, 'player');
    }

    public static function _listSyncTopicForStreamInfo($stream_id)
    {
        $admin_id = StreamBase::admin_id($stream_id);
        $topicList = self::_listSyncTopicByAdminId($admin_id);
        return self::_autoAddTopicHash($topicList, 'stream');
    }

    public static function _listSyncTopicForRoomInfo($room_id)
    {
        $admin_id = LiveRoom::admin_id($room_id);
        $topicList = self::_listSyncTopicByAdminId($admin_id);
        return self::_autoAddTopicHash($topicList, 'room');
    }

    public static function _listSyncTopicForAdminInfo($admin_id)
    {
        $topicList = self::_listSyncTopicByAdminId($admin_id);
        return self::_autoAddTopicHash($topicList, 'admin');
    }

    private static function _listSyncTopicByAdminId($admin_id)
    {
        $topicList = [];
        // 广播话题  admin  相关信息改动 只会向上级管理员 广播
        if (Api::_isSuper($admin_id)) {
            // 跳过 super 改动只发给自己
        } elseif (Api::_isAgent($admin_id)) {
            // 代理的改动 依次向 super 广播
            $topicList[] = self::_superAllTopic();
            $agent_id = $admin_id;
            $topicList[] = self::_agentAllTopic($agent_id);
        } elseif (Api::_isParent($admin_id)) {
            // 代理的改动 依次向 super agent 广播
            $topicList[] = self::_superAllTopic();
            $parent_id = $admin_id;
            $agent_id = AdminUser::agent_id($parent_id);
            $topicList[] = self::_agentAllTopic($agent_id);
            $topicList[] = self::_parentAllTopic($parent_id);
        } elseif (Api::_isSub($admin_id)) {
            // 代理的改动 依次向 super agent parent 广播
            $topicList[] = self::_superAllTopic();
            $sub_id = $admin_id;
            $parent_id = AdminUser::parent_id($sub_id);
            $agent_id = AdminUser::agent_id($parent_id);
            $topicList[] = self::_agentAllTopic($agent_id);
            $topicList[] = self::_parentAllTopic($parent_id);
            $topicList[] = self::_subAllTopic($sub_id);
        }
        $topicList[] = self::_selfAdminTopic($admin_id);
        return $topicList;
    }

    private static function _autoAddTopicHash(array $topicList, $prefix = '', $salt = "salt_topic")
    {
        $countly_pre = App::config('ENV_WEB.countly_pre', 'steel');
        $ret = [];
        $pre = !empty($prefix) ? Util::trimlower("{$countly_pre}_{$prefix}_") : Util::trimlower("{$countly_pre}_");
        foreach ($topicList as $topic) {
            $ret[] = "{$pre}{$topic}_" . Util::short_md5("{$pre}_{$topic}_{$salt}", 8);
        }
        return $ret;
    }

    public static function _selfAdminTopic($admin_id)
    {
        return "admin_{$admin_id}";
    }

    public static function _superAllTopic()
    {
        return AdminTypeEnum::SUPER_VALUE . "_all";
    }

    public static function _agentAllTopic($admin_id)
    {
        return AdminTypeEnum::AGENT_VALUE . "_{$admin_id}";
    }

    public static function _parentAllTopic($admin_id)
    {
        false && func_get_args();
        return AdminTypeEnum::PARENT_VALUE . "_{$admin_id}";
    }

    public static function _subAllTopic($admin_id)
    {
        false && func_get_args();
        return AdminTypeEnum::SUB_VALUE . "_{$admin_id}";
    }


    private static function _buildAppRouter($admin_id)
    {
        $admin_slug = AdminUser::admin_slug($admin_id);
        $admin_type = AdminUser::admin_type($admin_id);
        $superAppRouter = static::_getAppRouterByAdminType(AdminTypeEnum::SUPER_VALUE);

        if (Api::_isSuper($admin_id)) {
            $agentAppRouter = static::_getAppRouterByAdminType(AdminTypeEnum::AGENT_VALUE);
            $parentAppRouter = static::_getAppRouterByAdminType(AdminTypeEnum::PARENT_VALUE);
            $subAppRouter = static::_getAppRouterByAdminType(AdminTypeEnum::SUB_VALUE);
        } elseif (Api::_isAgent($admin_id)) {
            $agentAppRouter = self::_buildMenuACLByDepsForAgent($admin_slug);
            $parent_slug = $admin_slug == AdminUser::SLUG_SELF ? AdminUser::SLUG_PARENT : '';

            $parentAppRouter = self::_buildMenuACLByDepsForParent($parent_slug, $admin_id);
            $subAppRouter = static::_getAppRouterByAdminType(AdminTypeEnum::SUB_VALUE);
        } elseif (Api::_isParent($admin_id)) {
            $agent_id = AdminUser::agent_id($admin_id);
            $agent_slug = AdminUser::admin_slug($agent_id);
            $sub_slug = '';

            $agentAppRouter = self::_buildMenuACLByDepsForAgent($agent_slug);
            $parentAppRouter = self::_buildMenuACLByDepsForParent($admin_slug, $agent_id);
            $subAppRouter = self::_buildMenuACLByDepsForSub($sub_slug, $agent_id, $admin_id);
        } else {  //  if (Api::_isSub($admin_id))
            $agent_id = AdminUser::agent_id($admin_id);
            $parent_id = AdminUser::parent_id($admin_id);
            $agent_slug = AdminUser::admin_slug($agent_id);
            $parent_slug = AdminUser::admin_slug($parent_id);

            $agentAppRouter = self::_buildMenuACLByDepsForAgent($agent_slug);
            $parentAppRouter = self::_buildMenuACLByDepsForParent($parent_slug, $agent_id);
            $subAppRouter = self::_buildMenuACLByDepsForSub($admin_slug, $agent_id, $parent_id);
        }

        list($appRouter, $appRouterACL) = self::_buildMenuByTag($admin_type, $admin_id);


        $appRouterAllACL = [
            AdminTypeEnum::SUPER_VALUE => $superAppRouter,
            AdminTypeEnum::AGENT_VALUE => $agentAppRouter,
            AdminTypeEnum::PARENT_VALUE => $parentAppRouter,
            AdminTypeEnum::SUB_VALUE => $subAppRouter,
        ];
        return [$appRouter, $appRouterACL, $appRouterAllACL];
    }

    public static function _buildMenuAllACL()
    {
        $ret = [];
        foreach (AdminTypeEnum::ALL_ENUM_VALUE as $tag) {
            $ret[$tag] = self::_buildMenuByTag($tag, 0, true)[0];
        }
        return $ret;
    }

    private static $_menu_cache_map = [];

    public static function _getAppRouterByAdminType($admin_type)
    {
        if (!isset(self::$_menu_cache_map[$admin_type])) {
            $config = App::config($admin_type);
            $menus = !empty($config['menus']) ? $config['menus'] : [];
            self::$_menu_cache_map[$admin_type] = self::_fixMenu($admin_type, $menus);
        }
        $appRouter = self::$_menu_cache_map[$admin_type];
        return $appRouter;
    }

    public static function _buildMenuByTag($admin_type, $admin_id = 0, $all = false)
    {
        $appRouter = static::_getAppRouterByAdminType($admin_type);


        if (!$all && $admin_id > 0) {
            if ($admin_type == AdminTypeEnum::SUPER_VALUE) {
                $appRouter = static::_buildMenuACLByDeps($appRouter, $admin_id);
                $appRouterACL = self::_aclMenu($appRouter, 1);
            } else {
                $appRouter = static::_buildMenuACLByDeps($appRouter, $admin_id);
                $_appRouterACL = self::_aclMenu($appRouter, 0);

                $path_set = Api::_loadACLByAdmin($admin_id, 'menu');
                $appRouterACL = Util::vl(Util::build_map($path_set), $_appRouterACL);
            }
        } else {
            $appRouterACL = self::_aclMenu($appRouter, 1);
        }
        $appRouterACL = Util::build_set($appRouterACL);

        return [$appRouter, $appRouterACL];
    }

    private static function _buildMenuACLByDepsForSuper($super_slug)
    {
        $appRouter = static::_getAppRouterByAdminType(AdminTypeEnum::SUPER_VALUE);
        $menuMap = [];

        $checkSlug = function ($m, $slug) {
            return empty($m['slug']) || (!empty($m['slug']) && in_array($slug, $m['slug']));
        };

        foreach ($appRouter as $menu) {
            $children = Util::v($menu, 'children', []);
            $menu['children'] = [];
            foreach ($children as $child) {
                if ($checkSlug($child, $super_slug)) {
                    $menu['children'][] = $child;
                }
            }
            if ($checkSlug($menu, $super_slug) && !empty($menu['children'])) {
                $menuMap[] = $menu;
            }
        }
        return $menuMap;
    }

    private static function _buildMenuACLByDepsForAgent($agent_slug)
    {
        $appRouter = static::_getAppRouterByAdminType(AdminTypeEnum::AGENT_VALUE);

        $menuMap = [];

        $checkSlug = function ($m, $slug) {
            return empty($m['slug']) || (!empty($m['slug']) && in_array($slug, $m['slug']));
        };

        foreach ($appRouter as $menu) {
            $children = Util::v($menu, 'children', []);
            $menu['children'] = [];
            foreach ($children as $child) {
                if ($checkSlug($child, $agent_slug)) {
                    $menu['children'][] = $child;
                }
            }
            if ($checkSlug($menu, $agent_slug) && !empty($menu['children'])) {
                $menuMap[] = $menu;
            }
        }
        return $menuMap;
    }

    private static function _buildMenuACLByDepsForParent($parent_slug, $agent_id)
    {
        $appRouter = static::_getAppRouterByAdminType(AdminTypeEnum::PARENT_VALUE);
        $menuMap = [];

        $checkSlug = function ($m, $slug) {
            return empty($m['slug']) || (!empty($m['slug']) && in_array($slug, $m['slug']));
        };

        $agentMenu = Util::build_map(Api::_loadACLByAdmin($agent_id, 'menu'));

        $checkDepSlug = function ($m, $slug) use ($agentMenu, $checkSlug) {
            $depList = Util::build_map_set(array_map(function ($v) {
                return join('.', $v);
            }, Util::v($m, 'dep', [])));
            return Util::allDepsInObj($depList, $agentMenu) && $checkSlug($m, $slug);
        };

        foreach ($appRouter as $menu) {
            $children = Util::v($menu, 'children', []);
            $menu['children'] = [];
            foreach ($children as $child) {
                if ($checkDepSlug($child, $parent_slug)) {
                    $menu['children'][] = $child;
                }
            }
            if ($checkDepSlug($menu, $parent_slug) && !empty($menu['children'])) {
                $menuMap[] = $menu;
            }
        }
        return $menuMap;
    }

    private static function _buildMenuACLByDepsForSub($sub_slug, $agent_id, $parent_id)
    {
        $appRouter = static::_getAppRouterByAdminType(AdminTypeEnum::SUB_VALUE);
        $menuMap = [];

        $checkSlug = function ($m, $slug) {
            return empty($m['slug']) || (!empty($m['slug']) && in_array($slug, $m['slug']));
        };

        $parentMenu = Util::build_map(Api::_loadACLByAdmin($parent_id, 'menu'));
        $agentMenu = Util::build_map(Api::_loadACLByAdmin($agent_id, 'menu'));

        $checkParentDepSlug = function ($m, $slug) use ($parentMenu, $agentMenu, $checkSlug) {
            $depList = Util::build_map_set(array_map(function ($v) {
                return join('.', $v);
            }, Util::v($m, 'dep', [])));

            return Util::allDepsInObj($depList, $parentMenu) && Util::allDepsInObj(Util::parentDepsFromMenu($depList, static::_getAppRouterByAdminType(AdminTypeEnum::PARENT_VALUE)), $agentMenu) && $checkSlug($m, $slug);
        };
        foreach ($appRouter as $menu) {
            $children = Util::v($menu, 'children', []);
            $menu['children'] = [];
            foreach ($children as $child) {
                if ($checkParentDepSlug($child, $sub_slug)) {
                    $menu['children'][] = $child;
                }
            }
            if ($checkParentDepSlug($menu, $sub_slug) && !empty($menu['children'])) {
                $menuMap[] = $menu;
            }
        }
        return $menuMap;
    }

    public static function _buildMenuACLByDeps($appRouter, $admin_id)
    {
        $admin_slug = AdminUser::admin_slug($admin_id);
        $agent_id = AdminUser::agent_id($admin_id);
        $parent_id = AdminUser::parent_id($admin_id);

        if (Api::_isAgent($admin_id)) {
            $menuMap = self::_buildMenuACLByDepsForAgent($admin_slug);
        } elseif (Api::_isParent($admin_id)) {
            $menuMap = self::_buildMenuACLByDepsForParent($admin_slug, $agent_id);
        } elseif (Api::_isSub($admin_id)) {
            $menuMap = self::_buildMenuACLByDepsForSub($admin_slug, $agent_id, $parent_id);
        } elseif (Api::_isSuper($admin_id)) {
            $menuMap = self::_buildMenuACLByDepsForSuper($admin_slug);
        } else {
            $menuMap = $appRouter;
        }

        return $menuMap;
    }

    private static $_default_icon = 'android-radio-button-off';

    private static function _aclMenu(array $menus, $default = 1)
    {
        $access_value_map = [];
        foreach ($menus as $menu) {
            $access_value = Util::v($menu, 'access_value');
            if (!empty($access_value)) {
                $access_value_map[$access_value] = $default;
            }
            $children = Util::v($menu, 'children', []);
            if (!empty($children)) {
                $tmp = self::_aclMenu($children, $default);
                $access_value_map = array_merge($access_value_map, $tmp);
            }
        }
        return $access_value_map;
    }


    private static function _fixMenu($tag, array $menus)
    {
        if (empty($menus)) {
            return [];
        }
        $out_menus = [];
        foreach ($menus as $menu) {
            if (empty($menu['icon'])) {
                $menu['icon'] = self::$_default_icon;
            }

            $menu = self::_fixMenuItem($menu, [$tag]);
            $out_menus[] = $menu;
        }
        return $out_menus;
    }

    private static function _fixMenuItem(array $menu, array $paths = [])
    {
        $path = Util::v($menu, 'path', '');
        if (empty($path)) {
            $path = 'unknown';
        }
        $menu['path'] = $path;
        $menu['name'] = Util::v($menu, 'name', $menu['path']);
        $menu['title'] = Util::v($menu, 'title', $menu['name']);
        if (empty($menu['component'])) {
            $menu['component'] = $paths;
            $menu['component'][] = $menu['name'];  // 优先使用 name 作为组件名
        }
        if (!empty($menu['component']) && is_array($menu['component'])) {
            $menu['component'] = Util::joinNotEmpty('/', $menu['component']);
        }

        if (empty($menu['component'])) {
            unset($menu['component']);
        } else {
            $menu['component'] = Util::stri_endwith($menu['component'], '.vue') ? $menu['component'] : "{$menu['component']}.vue";
        }

        $children = Util::v($menu, 'children', []);
        $out_children = [];
        $paths[] = $menu['path'];
        //$menu['access_value'] = Util::joinNotEmpty('-', $paths);
        $menu['access_value'] = Util::joinNotEmpty('.', $paths);
        foreach ($children as $child) {
            $out_children[] = self::_fixMenuItem($child, $paths);
        }
        $menu['children'] = $out_children;
        if (empty($menu['children'])) {
            unset($menu['children']);
            if (count($paths) <= 2) {
                $menu['children'] = [
                    [
                        'path' => Util::str_startwith($menu['path'], '/') ? $menu['path'] : "/{$menu['path']}",
                        'title' => $menu['title'],
                        'name' => "{$menu['name']}_index",
                        'component' => $menu['component'],
                        'access_value' => $menu['access_value'],
                    ]
                ];
                $menu['component'] = 'Main';
            }
        } else { // 对于有子菜单的选项 设置组件为 Main
            $menu['component'] = 'Main';
        }

        if ($menu['component'] == 'Main') {
            $menu['path'] = Util::str_startwith($menu['path'], '/') ? $menu['path'] : "/{$menu['path']}";
        }
        return $menu;
    }
}