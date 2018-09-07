<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/3/8 0008
 * Time: 14:01
 */

if (!function_exists('__agent_menus__')) {
    function __agent_menus__()
    {
        return [
            [
                'path' => 'home',
                'icon' => 'home',
                'title' => '平台首页',
            ], [
                'path' => 'parent',
                'icon' => 'earth',
                'title' => '客户管理',
                'children' => [
                    [
                        'path' => 'parent-list',
                        'icon' => 'earth',
                        'title' => '客户管理',
                    ],
                    [
                        'path' => 'parent-acl',
                        'icon' => 'wrench',
                        'title' => '客户权限',
                    ],
                ]
            ], [
                'path' => 'room',
                'icon' => 'cube',
                'title' => '频道管理',
                'children' => [
                    [
                        'path' => 'room-agent',
                        'icon' => 'ios-color-filter',
                        'title' => '频道管理',
                    ], [
                        'path' => 'vod-agent',
                        'icon' => 'videocamera',
                        'title' => '直播录制',
                    ],
                ],
            ], [
                'path' => 'mcsmgr',
                'icon' => 'ios-videocam',
                'title' => '直播账号管理',
            ], [
                'path' => 'player',
                'icon' => 'home',
                'title' => '播放器管理',
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
            ], [
                'path' => 'figure',
                'icon' => 'pie-graph',
                'title' => '数据中心',
                'children' => [
                    [
                        'path' => 'site',
                        'icon' => 'arrow-graph-up-right',
                        'title' => '全站数据',
                    ], [
                        'path' => 'record',
                        'icon' => 'ios-paw',
                        'title' => '观看记录',
                    ], [
                        'path' => 'daily',
                        'icon' => 'android-clipboard',
                        'title' => '并发详情',
                    ], [
                        'path' => 'host',
                        'icon' => 'ios-pie',
                        'title' => '来源详情',
                    ], [
                        'path' => 'peak',
                        'icon' => 'ios-star',
                        'title' => '频道峰值',
                    ], [
                        'path' => 'parent-peak',
                        'icon' => 'ios-medical',
                        'title' => '客户峰值',
                    ],/* [
                        'path' => 'publish',
                        'icon' => 'ribbon-a',
                        'title' => '直播时间',
                    ], */
                ]
            ], [
                'path' => 'cash',
                'icon' => 'pricetag',
                'title' => '套餐数据',
                'slug' => [
                    \app\Model\AdminUser::SLUG_AGENT
                ],
                'children' => [
                    [
                        'path' => 'running',
                        'icon' => 'ios-paper-outline',
                        'title' => '并发流水',
                    ], [
                        'path' => 'package',
                        'icon' => 'ios-information',
                        'title' => '激活套餐',
                    ], [
                        'path' => 'purchase',
                        'icon' => 'pricetags',
                        'title' => '扣费记录',
                    ],
                ]
            ],
        ];
    }
}

if (!function_exists('__agent_acl__')) {
    function __agent_acl__()
    {
        return [];
    }
}

return [
    'menus' => __agent_menus__(),
    'acl' => __agent_acl__(),
];
