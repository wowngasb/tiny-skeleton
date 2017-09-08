<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL_\Type;

use app\api\GraphQL_\Types;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;/**
 * Class PlayerMpsConfig
 * 直播活动 Mps播放器
 * @package app\api\GraphQL_\Type
 */
class PlayerMpsConfig extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = Types::class;
        }

        $_description = !empty($_config['description']) ? $_config['description'] : "直播活动 Mps播放器";
        $_fields = !empty($_config['fields']) ? $_config['fields'] : [];
        
        $config = [
            'description' => $_description,
            'fields' => function () use ($type, $_fields) {
                $fields = [];
                $fields['player_type'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "播放器类型 固定为 mpsplayer",
                ];
                $fields['uin'] = [
                    'type' => $type::nonNull($type::Int()),
                    'description' => "用户奥点uin",
                ];
                $fields['appId'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "mps实例id 需要静态实例",
                ];
                $fields['autostart'] = [
                    'type' => $type::nonNull($type::Int()),
                    'description' => "是否自动播放",
                ];
                $fields['stretching'] = [
                    'type' => $type::nonNull($type::Int()),
                    'description' => "设置全屏模式 1代表按比例撑满至全屏 2代表铺满全屏 3代表视频原始大小",
                ];
                $fields['mobilefullscreen'] = [
                    'type' => $type::nonNull($type::Int()),
                    'description' => "移动端是否全屏",
                ];
                $fields['controlbardisplay'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "是否显示控制栏 可取值 disable enable 默认为disable",
                ];
                $fields['isclickplay'] = [
                    'type' => $type::nonNull($type::Int()),
                    'description' => "是否单击播放，默认为false",
                ];
                $fields['isfullscreen'] = [
                    'type' => $type::nonNull($type::Int()),
                    'description' => "是否双击全屏，默认为true",
                ];
                return array_merge($fields, $_fields);
            },
            'resolveField' => function($value, $args, $context, ResolveInfo $info) {
                if (method_exists($this, $info->fieldName)) {
                    return $this->{$info->fieldName}($value, $args, $context, $info);
                } else {
                    return is_array($value) ? $value[$info->fieldName] : $value->{$info->fieldName};
                }
            },
        ];
        parent::__construct($config);
    }

}