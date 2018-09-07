<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/3/8 0008
 * Time: 14:01
 */

if (!function_exists('__sub_menus__')) {
    function __sub_menus__()
    {
        return [
            [
                'path' => 'home',
                'icon' => 'home',
                'title' => '平台首页',
                'dep' => [
                    ['parent', 'home']
                ]
            ], [
                'path' => 'room',
                'icon' => 'cube',
                'title' => '频道中心',
                'dep' => [
                    ['parent', 'room']
                ],
                'children' => [
                    [
                        'path' => 'list',
                        'icon' => 'ios-browsers',
                        'title' => '频道管理',
                        'dep' => [
                            ['parent', 'room', 'list']
                        ]
                    ], [
                        'path' => 'vod-sub',
                        'icon' => 'videocamera',
                        'title' => '直播录制',
                        'dep' => [
                            ['parent', 'room', 'vod-parent']
                        ]
                    ],
                ]
            ], [
                'path' => 'figure',
                'icon' => 'pie-graph',
                'title' => '数据中心',
                'dep' => [
                    ['parent', 'figure']
                ],
                'children' => [
                    [
                        'path' => 'site',
                        'icon' => 'arrow-graph-up-right',
                        'title' => '直播概况',
                        'dep' => [
                            ['parent', 'figure', 'site']
                        ]
                    ], [
                        'path' => 'record',
                        'icon' => 'ios-paw',
                        'title' => '观看记录',
                        'dep' => [
                            ['parent', 'figure', 'record']
                        ]
                    ], [
                        'path' => 'daily',
                        'icon' => 'android-clipboard',
                        'title' => '并发详情',
                        'dep' => [
                            ['parent', 'figure', 'daily']
                        ]
                    ], [
                        'path' => 'host',
                        'icon' => 'ios-pie',
                        'title' => '来源详情',
                        'dep' => [
                            ['parent', 'figure', 'host']
                        ]
                    ], [
                        'path' => 'peak',
                        'icon' => 'ios-star',
                        'title' => '频道峰值',
                        'dep' => [
                            ['parent', 'figure', 'peak']
                        ]
                    ],
                ]
            ],
        ];
    }
}

if (!function_exists('__sub_acl__')) {
    function __sub_acl__()
    {
        return [];
    }
}

return [
    'menus' => __sub_menus__(),
    'acl' => __sub_acl__(),
];
