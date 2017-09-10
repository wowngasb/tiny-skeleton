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
 * Class ChatConfig
 * 直播活动 聊天配置信息
 * @package app\api\GraphQL_\Type
 */
class ChatConfig extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = Types::class;
        }

        $_description = !empty($_config['description']) ? $_config['description'] : "直播活动 聊天配置信息";
        $_fields = !empty($_config['fields']) ? $_config['fields'] : [];
        
        $config = [
            'description' => $_description,
            'fields' => function () use ($type, $_fields) {
                $fields = [];
                $fields['review_type'] = [
                    'type' => $type::nonNull($type::ReviewTypeEnum()),
                    'description' => "房间聊天审核类型 禁止聊天 disable_chat, 关闭聊天审核，聊天直接发布 direct_pub, 开启聊天审核 review_chat",
                ];
                $fields['sysmsg_type'] = [
                    'type' => $type::nonNull($type::SysMsgTypeEnum()),
                    'description' => "房间系统消息显示类型 全部显示 show_all, 全部隐藏 hide_all",
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