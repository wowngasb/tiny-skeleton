<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/3/8 0008
 * Time: 14:01
 */

if (!function_exists('__super_menus__')) {
    function __super_menus__()
    {
        return [
            [
                'path' => 'home',
                'icon' => 'home',
                'title' => '平台首页',
            ], [
                'path' => 'supercfg',
                'icon' => 'android-settings',
                'title' => '权限管理',
                'children' => [
                    [
                        'path' => 'agent-acl',
                        'icon' => 'settings',
                        'title' => '代理权限',
                    ],
                    [
                        'path' => 'parent-acl',
                        'icon' => 'wrench',
                        'title' => '客户权限',
                    ],
                    [
                        'path' => 'support-acl',
                        'icon' => 'playstation',
                        'title' => '自营客户权限',
                    ],
                    [
                        'path' => 'sub-acl',
                        'icon' => 'hammer',
                        'title' => '子帐号权限',
                    ],
                ]
            ], [
                'path' => 'agentmgr',
                'icon' => 'person-stalker',
                'title' => '客户管理',
                'children' => [
                    [
                        'path' => 'list',
                        'icon' => 'person',
                        'title' => '代理管理',
                    ], [
                        'path' => 'parent',
                        'icon' => 'earth',
                        'title' => '客户管理',
                        'slug' => [
                            \app\Model\AdminUser::SLUG_SUPER
                        ],
                    ], [
                        'path' => 'support',
                        'icon' => 'ios-filing',
                        'title' => '自营客户',
                    ], [
                        'path' => 'sub',
                        'icon' => 'shuffle',
                        'title' => '子账号管理',
                        'slug' => [
                            \app\Model\AdminUser::SLUG_SUPER
                        ],
                    ],
                ]
            ], [
                'path' => 'room',
                'icon' => 'cube',
                'title' => '频道管理',
                'children' => [
                    [
                        'path' => 'room-list',
                        'icon' => 'ios-color-filter',
                        'title' => '频道管理',
                    ], [
                        'path' => 'room-support',
                        'icon' => 'ios-color-filter-outline',
                        'title' => '自营频道',
                    ], [
                        'path' => 'room-vod',
                        'icon' => 'videocamera',
                        'title' => '直播录制',
                    ],
                ]
            ], [
                'path' => 'mcsmgr',
                'icon' => 'ios-videocam',
                'title' => '直播账号管理',
            ], [
                'path' => 'player',
                'icon' => 'home',
                'title' => '播放器管理',
                'slug' => [
                    \app\Model\AdminUser::SLUG_SUPER
                ],
                'children' => [
                    [
                        'path' => 'playerdefault',
                        'icon' => 'ios-videocam-outline',
                        'title' => '默认播放器',
                    ],
                    [
                        'path' => 'playermanage',
                        'icon' => 'ios-videocam',
                        'title' => '播放器管理',
                    ],
                    [
                        'path' => 'player-acl',
                        'icon' => 'videocamera',
                        'title' => '播放器鉴权',
                    ],
                ]
            ],[
                'path' => 'figure',
                'icon' => 'pie-graph',
                'title' => '数据中心',
                'slug' => [
                    \app\Model\AdminUser::SLUG_SUPER
                ],
                'children' => [
                    [
                        'path' => 'site',
                        'icon' => 'arrow-graph-up-right',
                        'title' => '全站数据',
                    ], [
                        'path' => 'tab-running',
                        'icon' => 'android-clipboard',
                        'title' => '并发详情',
                    ], [
                        'path' => 'peak',
                        'icon' => 'ios-star',
                        'title' => '频道峰值',
                    ], [
                        'path' => 'parent-peak',
                        'icon' => 'ios-medical',
                        'title' => '客户峰值',
                    ], [
                        'path' => 'record',
                        'icon' => 'ios-paw',
                        'title' => '观看记录',
                    ],
                ]
            ], [
                'path' => 'xdyisv',
                'icon' => 'pricetag',
                'title' => '套餐数据',
                'slug' => [
                    \app\Model\AdminUser::SLUG_SUPER
                ],
                'children' => [
                    [
                        'path' => 'agent-running',
                        'icon' => 'ios-paper-outline',
                        'title' => '代理客户并发',
                    ], [
                        'path' => 'agent-package',
                        'icon' => 'ios-heart',
                        'title' => '代理客户套餐',
                    ], [
                        'path' => 'agent-purchase',
                        'icon' => 'pricetags',
                        'title' => '代理客户扣费',
                    ], [
                        'path' => 'parent-running',
                        'icon' => 'ios-list-outline',
                        'title' => '自营客户并发',
                    ], [
                        'path' => 'parent-package',
                        'icon' => 'ios-heart-outline',
                        'title' => '自营客户套餐',
                    ],[
                        'path' => 'parent-purchase',
                        'icon' => 'ios-pricetags-outline',
                        'title' => '自营客户扣费',
                    ],
                ]
            ], [
                'path' => 'sitemgr',
                'icon' => 'nuclear',
                'title' => '网站管理员',
                'slug' => [
                    \app\Model\AdminUser::SLUG_SUPER
                ],
                'children' => [
                    [
                        'path' => 'mgr-list',
                        'icon' => 'ios-paper-outline',
                        'title' => '管理账号',
                    ], [
                        'path' => 'customer-service',
                        'icon' => 'ios-heart',
                        'title' => '客服账号',
                    ],
                ]
            ], [
                'path' => 'lss',
                'icon' => 'ios-world-outline',
                'title' => '流媒体中心',
            ]
        ];
    }
}

if (!function_exists('__super_acl__')) {
    function __super_acl__()
    {
        return [];
    }
}

return [
    'menus' => __super_menus__(),
    'acl' => __super_acl__(),
];
