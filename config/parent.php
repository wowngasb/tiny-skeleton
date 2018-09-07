<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/3/8 0008
 * Time: 14:01
 */

if (!function_exists('__parent_menus__')) {
    function __parent_menus__()
    {
        return [
            [
                'path' => 'home',
                'icon' => 'home',
                'title' => '平台首页',
                'dep' => [
                    ['agent', 'home']
                ]
            ], [
                'path' => 'room',
                'icon' => 'ios-browsers',
                'title' => '频道中心',
                'dep' => [
                    ['agent', 'room'],
                ],
                'children' => [
                    [
                        'path' => 'list',
                        'icon' => 'cube',
                        'title' => '频道管理',
                        'dep' => [
                            ['agent', 'room', 'room-agent'],
                        ],
                    ], [
                        'path' => 'vod-parent',
                        'icon' => 'videocamera',
                        'title' => '直播录制',
                        'dep' => [
                            ['agent', 'room', 'vod-agent'],
                        ],
                    ],
                ]
            ], [
                'path' => 'sub',
                'icon' => 'person-stalker',
                'title' => '子账号中心',
                'children' => [
                    [
                        'path' => 'mgr',
                        'icon' => 'person',
                        'title' => '子账号管理',
                    ],
                    [
                        'path' => 'sub-acl',
                        'icon' => 'wrench',
                        'title' => '子账号权限',
                    ],
                ]
            ], [
                'path' => 'mcsmgr',
                'icon' => 'ios-videocam',
                'title' => '直播账号',
                'dep' => [
                    ['agent', 'mcsmgr']
                ]
            ], [
                'path' => 'player',
                'icon' => 'home',
                'title' => '播放器管理',
                'children' => [
                    [
                        'path' => 'playermanage',
                        'icon' => 'person',
                        'title' => '播放器管理',
                    ], [
                        'path' => 'player-acl',
                        'icon' => 'videocamera',
                        'title' => '播放器鉴权',
                    ],
                ],
            ],[
                'path' => 'figure',
                'icon' => 'pie-graph',
                'title' => '数据中心',
                'dep' => [
                    ['agent', 'figure'],
                ],
                'children' => [
                    [
                        'path' => 'site',
                        'icon' => 'arrow-graph-up-right',
                        'title' => '直播概况',
                        'dep' => [
                            ['agent', 'figure', 'site'],
                        ],
                    ], [
                        'path' => 'record',
                        'icon' => 'ios-paw',
                        'title' => '观看记录',
                        'dep' => [
                            ['agent', 'figure', 'record'],
                        ],
                    ], [
                        'path' => 'daily',
                        'icon' => 'android-clipboard',
                        'title' => '并发详情',
                        'dep' => [
                            ['agent', 'figure', 'daily'],
                        ],
                    ], [
                        'path' => 'host',
                        'icon' => 'ios-pie',
                        'title' => '来源详情',
                        'dep' => [
                            ['agent', 'figure', 'host'],
                        ],
                    ], [
                        'path' => 'peak',
                        'icon' => 'ios-star',
                        'title' => '频道峰值',
                        'dep' => [
                            ['agent', 'figure', 'peak'],
                        ],
                    ], [
                        'path' => 'parent-peak',
                        'icon' => 'ios-medical',
                        'title' => '客户峰值',
                    ],
                ]
            ], [
                'path' => 'cash',
                'icon' => 'pricetag',
                'title' => '套餐数据',
                'slug' => [
                    \app\Model\AdminUser::SLUG_PARENT
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

if (!function_exists('__parent_acl__')) {
    function __parent_acl__()
    {
        return [];
    }
}

return [
    'menus' => __parent_menus__(),
    'acl' => __parent_acl__(),
];
