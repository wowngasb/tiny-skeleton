<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/10/25
 * Time: 15:38
 */

namespace app\api\Abstracts;


use app\AdminController;
use app\api\GraphQL_\Enum\StateEnum;
use app\api\GraphQL_\Enum\StreamTypeEnum;
use app\Exception\ApiAuthError;
use app\Libs\AdminAuth;
use app\Model\AdminAccessControl;
use app\Model\StreamBase;
use app\Util;
use Tiny\OrmQuery\Q;
use Tiny\Plugin\DevAuthController;

abstract class Api extends AbstractApi
{

    public static function _setStreamBaseIdByMcsArgs($mcs_account, $mcs_password, $mcs_vhost, $mcs_app, $mcs_stream)
    {
        $stream_type = StreamTypeEnum::STREAM_MCS_VALUE;
        $mcsWhere = [
            'mcs_account' => Q::where("%{$mcs_account}%", 'like', function () use ($mcs_account) {
                return !empty($mcs_account);
            }),
            'mcs_password' => Q::where("%{$mcs_password}%", 'like', function () use ($mcs_password) {
                return !empty($mcs_password);
            }),
            'mcs_vhost' => Q::where("%{$mcs_vhost}%", 'like', function () use ($mcs_vhost) {
                return !empty($mcs_vhost);
            }),
            'mcs_app' => Q::where("%{$mcs_app}%", 'like', function () use ($mcs_app) {
                return !empty($mcs_app);
            }),
            'mcs_stream' => Q::where("%{$mcs_stream}%", 'like', function () use ($mcs_stream) {
                return !empty($mcs_stream);
            }),
            'stream_type' => Q::where($stream_type, '=', function () use ($stream_type) {
                return !empty($stream_type);
            }),
        ];
        return StreamBase::_pluck(StreamBase::tableBuilder($mcsWhere), 'stream_id');
    }

    public static function _setAllMenuACLByAdminId($admin_type, $admin_id, $state = StateEnum::NORMAL_VALUE, $pre_fix = '')
    {
        $acl = AdminController::_buildMenuByTag($admin_type, $admin_id, true)[1];
        $menuACL = Util::build_map($acl);
        $tag = AdminAccessControl::ACCESS_TYPE_MENU;
        $info = [];
        foreach ($menuACL as $ck => $cv) {
            if (!empty($pre_fix) && !Util::stri_startwith($ck, $pre_fix)) {
                continue;
            }
            $info["{$tag}.{$ck}"] = AdminAccessControl::upsertItem([
                'admin_id' => $admin_id,
                'access_type' => $tag,
                'access_value' => $ck
            ], [
                'admin_id' => $admin_id,
                'access_type' => $tag,
                'access_value' => $ck,
                'state' => $state
            ]);
        }
        return $info;
    }

    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);

        if (DevAuthController::_checkDevelopKey($this->getRequest())) {
            // 开发模式 允许设置 hook_id 以指定用户权限来执行API
            $hook_id = Util::v($params, 'hook_id');
            if (!empty($hook_id)) {
                if (!$this->auth()->onceUsingId($hook_id)) {
                    throw new ApiAuthError("Api auth()->onceUsingId Error with hook_id:{$hook_id}");
                }
            } else {
                $admin_id = self::_tryFindFirstSuperId();
                !empty($admin_id) && $this->auth()->onceUsingId($admin_id);
            }
        }

        $token = $this->getRequest()->_header('authorization', '');
        $token = !empty($token) ? $token : Util::v($params, 'token', '');
        if (!empty($token)) {
            $agent = $this->getRequest()->_server('HTTP_USER_AGENT', '');
            $agent = Util::short_md5(Util::trimlower($agent), 16);
            $auth_id = AdminAuth::_decode($token, $agent);
            if (!empty($auth_id)) {
                if (!$this->auth()->onceUsingId($auth_id)) {
                    throw new ApiAuthError("Api auth()->onceUsingId Error with auth_id:{$auth_id}");
                }
            } else {
                throw new ApiAuthError("Api AdminUser::_decode Error with token:{$token}");
            }
        }

        return $params;
    }

}