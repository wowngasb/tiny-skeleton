<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL_\Type;

use app\api\GraphQL_\Types;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class MsgDonateAndGift
 * 打赏及赠送礼物消息
 * @package app\api\GraphQL_\Type
 */
class MsgDonateAndGift extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = new Types();
        }
        $config = [
            'description' => "打赏及赠送礼物消息",
            'fields' => []
        ];
        $config['fields']['msg_type'] = [
            'type' => $type::nonNull($type::String()),
            'description' => "互动消息类型 固定为 donate_and_gift",
        ];
        $config['fields']['trade_type'] = [
            'type' => $type::nonNull($type::String()),
            'description' => "打赏或礼物类型 ",
        ];
        $config['fields']['trade_num'] = [
            'type' => $type::nonNull($type::Float()),
            'description' => "打赏或礼物数量 ",
        ];
        $config['fields']['content_text'] = [
            'type' => $type::nonNull($type::String()),
            'description' => "消息文本内容",
        ];
        $config['fields']['target_user'] = [
            'type' => $type::BasicUser([], $type),
            'description' => "目标用户 信息",
        ];
        
        $config['resolveField'] = function($value, $args, $context, ResolveInfo $info) {
            if (method_exists($this, $info->fieldName)) {
                return $this->{$info->fieldName}($value, $args, $context, $info);
            } else {
                return is_array($value) ? $value[$info->fieldName] : $value->{$info->fieldName};
            }
        };
        if (!empty($_config['fields'])) {
            $config['fields'] = array_merge($_config['fields'], $config['fields']);
        }
        parent::__construct($config);
    }

}