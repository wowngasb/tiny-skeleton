<?php
/**
 * Created by table_graphQL.
 * 用于PHP Tiny框架
 * Date: 2017-09
 */
namespace app\api\GraphQL_\Type;

use app\api\GraphQL_\Types;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;/**
 * Class PlayerAodianConfig
 * 直播活动 奥点播放器
 * @package app\api\GraphQL_\Type
 */
class PlayerAodianConfig extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = Types::class;
        }

        $_description = !empty($_config['description']) ? $_config['description'] : "直播活动 奥点播放器";
        $_fields = !empty($_config['fields']) ? $_config['fields'] : [];
        
        $config = [
            'description' => $_description,
            'fields' => function () use ($type, $_fields) {
                $fields = [];
                $fields['player_type'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "播放器类型 固定为 aodianplayer",
                ];
                $fields['rtmpUrl'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "控制台开通的APP rtmp地址 必要参数",
                ];
                $fields['hlsUrl'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "控制台开通的APP hls地址 必要参数",
                ];
                $fields['autostart'] = [
                    'type' => $type::nonNull($type::Int()),
                    'description' => "是否自动播放",
                ];
                $fields['bufferlength'] = [
                    'type' => $type::nonNull($type::Int()),
                    'description' => "视频缓冲时间 默认为1秒",
                ];
                $fields['maxbufferlength'] = [
                    'type' => $type::nonNull($type::Int()),
                    'description' => "最大视频缓冲时间 默认为2秒",
                ];
                $fields['stretching'] = [
                    'type' => $type::nonNull($type::Int()),
                    'description' => "设置全屏模式 1代表按比例撑满至全屏 2代表铺满全屏 3代表视频原始大小",
                ];
                $fields['controlbardisplay'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "是否显示控制栏 可取值 disable enable 默认为disable",
                ];
                $fields['defvolume'] = [
                    'type' => $type::nonNull($type::Int()),
                    'description' => "默认音量",
                ];
                $fields['adveDeAddr'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "封面图地址",
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