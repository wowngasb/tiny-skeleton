<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/3/9 0009
 * Time: 9:20
 */

namespace app\api;


use app\api\Abstracts\AbstractApi;
use app\api\Abstracts\Api;
use app\api\GraphQL_\Enum\AdminTypeEnum;
use app\api\GraphQL_\Enum\StateEnum;
use app\Exception\ApiAuthBeyondError;
use app\Exception\ApiAuthError;
use app\Exception\ApiError;
use app\Exception\ApiParamsError;
use app\Exception\NeverRunAtHereError;
use app\Libs\SteelEncrypter;
use app\Model\AdminRecord;
use app\Model\AdminUser;
use app\Util;
use Tiny\Interfaces\RequestInterface;

class AuthMgr extends AbstractApi
{

    const AUTHCODE_EXPIRE = 600;
    const CHECKCODE_NAME = 'checkcode_pic';
    const AUTHCODE_NAME = 'AuthCode';


    public static $msg_interval = 60;
    public static $allow_type = ['telephone' => '短信', 'email' => '邮箱'];

    ######################################################
    #######################  会话函数 #####################
    ######################################################

    /**
     * 验证 图形验证码
     * @param RequestInterface $request
     * @param string $checkcode
     * @return bool
     */
    public static function _validCheckCode(RequestInterface $request, $checkcode)
    {
        if (empty($checkcode)) {
            return false;
        }
        $checkcode = strtolower($checkcode);
        $_checkcode = $request->_session(self::CHECKCODE_NAME, '');
        if ($checkcode == $_checkcode) {
            return true;
        }
        return false;
    }

    /**
     * 设置 图形验证码
     * @param RequestInterface $request
     * @param string $checkcode
     */
    public static function _setCheckCode(RequestInterface $request, $checkcode)
    {
        $request->set_session(self::CHECKCODE_NAME, $checkcode);
    }

    /**
     * 清除 图形验证码
     * @param RequestInterface $request
     */
    public static function _clearCheckCode(RequestInterface $request)
    {
        $request->del_session(self::CHECKCODE_NAME);
    }

    /**
     * 设置 短信验证码
     * @param int $admin_id admin ID
     * @param string $type telphone 或者 email
     * @param $sms_info
     */
    private function _setAdminAuthCode($admin_id, $type, $sms_info)
    {
        $s_key = self::AUTHCODE_NAME . "_{$admin_id}_{$type}";
        $this->set_session($s_key, $sms_info);
    }

    /**
     * 获取 短信验证码
     * @param int $admin_id admin ID
     * @param string $type telphone 或者 email
     * @return array|string
     */
    private function _getAdminAuthCode($admin_id, $type)
    {
        $s_key = self::AUTHCODE_NAME . "_{$admin_id}_{$type}";
        $tmp = $this->_session($s_key, []);
        return !empty($tmp) && is_array($tmp) ? $tmp : [];
    }

    /**
     * 清除 短信验证码
     * @param int $admin_id admin ID
     * @param string $type telphone 或者 email
     */
    private function _clearAdminAuthCode($admin_id, $type)
    {
        $s_key = self::AUTHCODE_NAME . "_{$admin_id}_{$type}";
        $this->del_session($s_key);
    }

    ################################################################
    ###########################  账号注册 API ##########################
    ################################################################

    /**
     * 检查 手机号 是否可用
     * @param string $phone_num 用户手机号 全局唯一
     * @return array ['msg' => '该手机号可以使用'];
     * @throws ApiParamsError
     */
    public function isFreeAdminPhoneNum($phone_num)
    {
        if (!self::_validPhoneNum($phone_num)) {
            throw new ApiParamsError('手机号格式错误');
        }
        $admin_id = AdminUser::checkItem($phone_num, 'name');  // 查找 帐号为该手机号的 用户
        if (!empty($admin_id)) {
            throw new ApiParamsError('该手机号已被使用');
        }
        $rst = ['msg' => '该手机号可以使用'];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 首页注册帐号 自动归属 自营账号下
     * @param string $phone_num 用户手机号
     * @param string $pasw 用户密码
     * @param string $authcode 手机验证码
     * @param string $token 代理推广链接带来的token
     * @return array ['msg' => '注册成功'];
     * @throws ApiError
     * @throws ApiParamsError
     */
    public function newAdminBySmsCode($phone_num, $pasw, $authcode, $token = '')
    {
        if (empty($pasw) || empty($phone_num) || empty($authcode)) {
            throw new ApiParamsError('参数错误');
        }

        self::isFreeAdminPhoneNum($phone_num);

        $agent_id = self::_tryFindFirstBaseAgentId();

        if (empty($agent_id)) {
            throw new ApiError("已关闭注册");
        }

        $business_id = intval(SteelEncrypter::_decode($token));

        $type = 'register';
        $this->checkTargetAuthCode($phone_num, $type, $authcode);

        $admin_id = AdminUser::createOne([
            'agent_id' => $agent_id,
            'business_id' => $business_id,
            'state' => StateEnum::NORMAL_VALUE,
            'register_from' => '手机注册',
            'name' => $phone_num,
            'title' => $phone_num,
            'cellphone' => $phone_num,
            'pasw' => $pasw,
            'avator' => self::$default_avator,
            'admin_type' => AdminTypeEnum::PARENT_VALUE,
            'admin_slug' => AdminUser::SLUG_PARENT,
            'nlimit_count_room' => Util::content('sitecfg.parent_count_room'),
            'nlimit_count_sub' => Util::content('sitecfg.parent_count_sub'),
            'vlimit_all_room' => Util::content('sitecfg.parent_limit'),
            'vlimit_online_num' => Util::content('sitecfg.parent_limit'),
            'account_credit' => Util::content('sitecfg.parent_credit'),
            'limit_onlinenumber_over_price' => Util::content('sitecfg.over_price'),
        ]);
        Util::update_admin_sub($admin_id);

        Api::_setAllMenuACLByAdminId(AdminTypeEnum::PARENT_VALUE, $admin_id, 1);

        $this->sessionLoginByPwd($phone_num, $pasw);

        return ['msg' => '注册成功'];
    }

    /**
     * 用于检查 验证码是否正确
     * @param string $target 注册时 设置为 手机号
     * @param string $type
     * @param string $authcode
     * @return array ['msg' => '验证码正确', 'data' => $sms_info];
     * @throws ApiParamsError
     */
    public function checkTargetAuthCode($target, $type, $authcode)
    {
        $sms_info = $this->_getAdminAuthCode($target, $type);
        $sms_info['last_sms_time'] = Util::v($sms_info, 'last_sms_time', time());

        if (time() - $sms_info['last_sms_time'] > self::AUTHCODE_EXPIRE) {  //验证码 有效期 默认十分钟
            throw new ApiParamsError('验证码已过期');
        }
        if ($sms_info['auth_code'] != $authcode) {
            throw new ApiParamsError('验证码错误');
        }

        $rst = ['msg' => '验证码正确', 'data' => $sms_info];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 用户注册 发送手机验证码
     * @param string $phone_num 用户手机号
     * @param string $checkcode 用户输入的 图形验证码
     * @return array ['msg'=>'验证码发送成功', 'msg_interval'=>self::$msg_interval];
     *                ['code' => 534, 'msg' => '发送过于频繁', 'interval' => self::$msg_interval - $interval, 'msg_interval' => self::$msg_interval];
     * @throws ApiError
     * @throws ApiParamsError
     */
    public function sendRegisterSmsCode($phone_num, $checkcode)
    {
        self::isFreeAdminPhoneNum($phone_num);

        if (!self::_validCheckCode($this->getRequest(), $checkcode)) {
            throw new ApiParamsError('图形验证码错误');
        }
        $type = 'register';
        $now = time();
        $sms_info = $this->_getAdminAuthCode($phone_num, $type);
        if (isset($sms_info['last_sms_time']) && $now - $sms_info['last_sms_time'] < self::$msg_interval) {
            $interval = $now - $sms_info['last_sms_time'];
            return ['code' => 534, 'msg' => '发送过于频繁', 'interval' => self::$msg_interval - $interval, 'msg_interval' => self::$msg_interval];
        }

        $sms_code = rand(100000, 999999);
        $sms_info = [
            'last_sms_time' => $now,
            'auth_code' => $sms_code,
        ];

        $tmp = self::_sendSmsAuthCode($phone_num, $sms_code);
        if (!$tmp) {
            throw new ApiError('验证码发送失败');
        }

        $this->_setAdminAuthCode($phone_num, $type, $sms_info);
        self::_clearCheckCode($this->getRequest());

        $rst = ['msg' => '验证码发送成功', 'msg_interval' => self::$msg_interval];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    ################################################################
    ###########################  账号验证 API ##########################
    ################################################################

    /**
     * 根据账号 获取验证方式
     * @param string $name 登陆账号 必选
     * @param string $checkcode 图形验证码 必选
     * @return array ['msg' => '获取成功',]
     * @throws ApiParamsError
     */
    public function getAuthByName($name, $checkcode)
    {
        if (empty($name)) {
            throw new ApiParamsError('参数错误');
        }
        if (!self::_validCheckCode($this->getRequest(), $checkcode)) {
            throw new ApiParamsError('图形验证码错误');
        }
        $admin_id = AdminUser::checkItem($name, 'name');
        if (empty($admin_id)) {
            throw new ApiParamsError('该用户不存在');
        }

        $rst = [
            'msg' => '获取成功',
            'data' => [
                'telephone' => Util::anonymous_telephone(AdminUser::cellphone($admin_id)),
                'email' => Util::anonymous_email(AdminUser::email($admin_id)),
            ],
        ];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 个人中心 发送验证码  用于 绑定或修改 个人信息
     * @param int $admin_id 管理员 id
     * @param string $type 支持  telephone  email  ['telephone' => '短信', 'email' => '邮箱'];
     * @param string $target 手机号 或 邮件地址
     * @return array ['msg' => '手机验证码发送成功', 'msg_interval' => self::$msg_interval];
     *               ['code' => 534, 'FlagString' => '发送过于频繁', 'interval' => self::$msg_interval - $interval, 'msg_interval' => self::$msg_interval];
     * @throws ApiAuthBeyondError
     * @throws ApiError
     * @throws ApiParamsError
     * @throws NeverRunAtHereError
     */
    public function sendPersonalAuthCode($admin_id, $type, $target)
    {
        $cur_admin_id = $this->auth()->id();
        self::_checkBeyondAdmin($cur_admin_id, $admin_id, __METHOD__ . " Error cur_admin_id:{$cur_admin_id}, admin_id:{$admin_id}");

        if (empty($type) || empty($target) || !isset(self::$allow_type[$type])) {
            throw new ApiParamsError('参数错误');
        }
        if ($type == 'telephone' && !self::_validPhoneNum($target)) {
            throw new ApiParamsError('手机号码格式错误');
        }
        if ($type == 'email' && !self::_validEmailAddress($target)) {
            throw new ApiParamsError('邮箱格式错误');
        }

        $now = time();
        $sms_info = $this->_getAdminAuthCode($admin_id, $type);
        if (isset($sms_info['last_sms_time']) && $now - $sms_info['last_sms_time'] < self::$msg_interval) {
            $interval = $now - $sms_info['last_sms_time'];
            return ['code' => 534, 'FlagString' => '发送过于频繁', 'interval' => self::$msg_interval - $interval, 'msg_interval' => self::$msg_interval];
        }

        $auth_code = rand(100000, 999999);
        $sms_info = [
            'last_sms_time' => $now,
            'auth_code' => strval($auth_code),
        ];

        $msg = '';
        if ($type == 'telephone') {
            $phone_num = $target;
            $tmp = self::_sendSmsAuthCode($phone_num, $auth_code);
            if (!$tmp) {
                throw new ApiError('手机验证码发送失败');
            }
            $msg = '手机验证码发送成功';
        } else if ($type == 'email') {
            $email = $target;
            $tmp = self::_sendEmailAuthCode($email, AdminUser::name($admin_id), $auth_code);
            if (!$tmp) {
                throw new ApiError('邮件验证码发送失败');
            }
            $msg = '邮箱验证码发送成功';
        }

        if (empty($msg)) {
            throw new NeverRunAtHereError("sendAuthCode Error type:{$type}");
        }

        $this->_setAdminAuthCode($admin_id, $type, $sms_info);
        self::_clearCheckCode($this->getRequest());

        $rst = [
            'msg' => $msg,
            'msg_interval' => self::$msg_interval
        ];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 设置用户的 手机号  需要提供验证码
     * @param int $admin_id 管理员 id
     * @param string $telephone 手机号码
     * @param string $authcode 认证码
     * @return array ['msg' => '设置手机成功'];
     * @throws ApiAuthBeyondError
     * @throws ApiParamsError
     */
    public function bindTelephoneByAuthCode($admin_id, $telephone, $authcode)
    {
        $cur_admin_id = $this->auth()->id();
        self::_checkBeyondAdmin($cur_admin_id, $admin_id, __METHOD__ . " Error cur_admin_id:{$cur_admin_id}, admin_id:{$admin_id}");

        $telephone = trim($telephone);
        if (empty($telephone)) {
            throw new ApiParamsError('参数错误');
        }

        if (!self::_validPhoneNum($telephone)) {
            throw new ApiParamsError('手机号码格式错误');
        }
        $type = 'telephone';
        $this->checkAuthCode(AdminUser::name($admin_id), $type, $authcode);

        $this->_clearAdminAuthCode($admin_id, $type);

        AdminUser::setOneById($admin_id, [
            'cellphone' => $telephone
        ]);
        $ip = $this->client_ip();
        $op_desc = '用户设置手机';
        AdminRecord::createOne([
            'room_id' => 0,    //  INTEGER  操作相关  room_id  尽可能 尝试记录
            'admin_id' => $admin_id,   //  INTEGER  操作者  admin_id  尽可能 尝试记录
            'op_type' => 3,    //  SMALLINT  操作类型  0 未知  1 登录  2 登出 3 其他操作
            'op_desc' => $op_desc,    //  VARCHAR(255)  field op_desc
            'op_ref' => $this->getRequest()->getHttpReferer(),    //  VARCHAR(255)  field op_ref
            'op_url' => $this->fullUrl(),    //  VARCHAR(255)  field op_url
            'op_args' => self::funcGetArgs(func_get_args(), __METHOD__),    //  TEXT  本次操作的 参数
            'op_ip' => $ip,    //  VARCHAR(20)  field op_ip
            'op_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
            'op_method' => __METHOD__,  // VARCHAR(255)  field op_method
            'op_admin_id' => $admin_id,    //  INTEGER  操作者  admin_id  尽可能 尝试记录
        ]);

        $rst = ['msg' => '设置手机成功'];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 设置用户的 邮箱  需要提供验证码
     * @param int $admin_id 管理员 id
     * @param string $email 邮箱地址
     * @param string $authcode 认证码
     * @return array ['msg' => '设置邮箱成功',];
     * @throws ApiAuthBeyondError
     * @throws ApiParamsError
     */
    public function bindEmailByAuthCode($admin_id, $email, $authcode)
    {
        $cur_admin_id = $this->auth()->id();
        self::_checkBeyondAdmin($cur_admin_id, $admin_id, __METHOD__ . " Error cur_admin_id:{$cur_admin_id}, admin_id:{$admin_id}");

        $email = trim($email);
        if (empty($email)) {
            throw new ApiParamsError('参数错误');
        }
        if (!self::_validEmailAddress($email)) {
            throw new ApiParamsError('邮箱格式错误');
        }
        $type = 'email';
        $this->checkAuthCode(AdminUser::name($admin_id), $type, $authcode);

        $this->_clearAdminAuthCode($admin_id, $type);

        AdminUser::setOneById($admin_id, [
            'email' => $email
        ]);
        $ip = $this->client_ip();
        $op_desc = '用户设置邮箱';
        AdminRecord::createOne([
            'room_id' => 0,    //  INTEGER  操作相关  room_id  尽可能 尝试记录
            'admin_id' => $admin_id,   //  INTEGER  操作者  admin_id  尽可能 尝试记录
            'op_type' => 3,    //  SMALLINT  操作类型  0 未知  1 登录  2 登出 3 其他操作
            'op_desc' => $op_desc,    //  VARCHAR(255)  field op_desc
            'op_ref' => $this->getRequest()->getHttpReferer(),    //  VARCHAR(255)  field op_ref
            'op_url' => $this->fullUrl(),    //  VARCHAR(255)  field op_url
            'op_args' => self::funcGetArgs(func_get_args(), __METHOD__),    //  TEXT  本次操作的 参数
            'op_ip' => $ip,    //  VARCHAR(20)  field op_ip
            'op_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
            'op_method' => __METHOD__,  // VARCHAR(255)  field op_method
            'op_admin_id' => $admin_id,    //  INTEGER  操作者  admin_id  尽可能 尝试记录
        ]);

        $rst = [
            'msg' => '设置邮箱成功',
            'data' => [
                'email' => AdminUser::email($admin_id),
            ]
        ];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    ################################################################
    ###########################  忘记密码 API ##########################
    ################################################################

    /**
     * 发送 忘记密码 需要 重置密码 的 短信或邮件验证码
     * @param string $name 登陆账号 必选
     * @param string $type 认证码 发送方式 目前支持 telephone 手机短信  email 邮件
     * @param string $checkcode 图形验证码 必选
     * @return array ['code'=> 0, 'msg'=>'发送成功']
     *               ['code' => 534, 'msg' => '发送过于频繁', 'interval' => self::$msg_interval - $interval, 'msg_interval' => self::$msg_interval];
     * @throws ApiError
     * @throws ApiParamsError
     * @throws NeverRunAtHereError
     */
    public function sendAuthCode($name, $type, $checkcode)
    {
        if (empty($name) || !isset(self::$allow_type[$type])) {
            throw new ApiParamsError('参数错误');
        }
        if (!self::_validCheckCode($this->getRequest(), $checkcode)) {
            throw new ApiParamsError('验证码错误');
        }
        $admin_id = AdminUser::checkItem($name, 'name');
        if (empty($admin_id)) {
            throw new ApiParamsError('该用户不存在');
        }
        $type_str = self::$allow_type[$type];
        if (($type == 'telephone' && empty(AdminUser::cellphone($admin_id))) || ($type == 'email' && empty(AdminUser::email($admin_id)))) {
            throw new ApiParamsError("该用户不支持{$type_str}认证");
        }

        $now = time();
        $sms_info = $this->_getAdminAuthCode($admin_id, $type);
        if (isset($sms_info['last_sms_time']) && $now - $sms_info['last_sms_time'] < self::$msg_interval) {
            $interval = $now - $sms_info['last_sms_time'];
            return ['code' => 534, 'msg' => '发送过于频繁', 'interval' => self::$msg_interval - $interval, 'msg_interval' => self::$msg_interval];
        }

        $auth_code = rand(100000, 999999);
        $sms_info = [
            'last_sms_time' => $now,
            'auth_code' => strval($auth_code),
        ];

        $msg = '';
        if ($type == 'telephone') {
            $phone_num = AdminUser::cellphone($admin_id);
            $tmp = self::_sendSmsAuthCode($phone_num, $auth_code);
            if (!$tmp) {
                throw new ApiError('手机验证码发送失败');
            }
            $msg = '手机验证码发送成功';
        } else if ($type == 'email') {
            $email = AdminUser::email($admin_id);
            $tmp = self::_sendEmailAuthCode($email, AdminUser::name($admin_id), $auth_code);
            if (!$tmp) {
                throw new ApiError('邮件验证码发送失败');
            }
            $msg = '邮箱验证码发送成功';
        }

        if (empty($msg)) {
            throw new NeverRunAtHereError("sendAuthCode Error type:{$type}");
        }

        $this->_setAdminAuthCode($admin_id, $type, $sms_info);
        self::_clearCheckCode($this->getRequest());

        $rst = [
            'msg' => $msg,
            'msg_interval' => self::$msg_interval
        ];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 检查 短信 或邮件验证码 是否正确
     * @param string $name 登陆账号 必选
     * @param string $type 发送方式 必选 目前支持 telephone 手机短信  email 邮件
     * @param string $authcode 短信 或者 邮件验证码 必选
     * @return array ['msg' => '验证码正确'];
     * @throws ApiParamsError
     */
    public function checkAuthCode($name, $type, $authcode)
    {
        if (empty($name) || empty($type) || empty($authcode)) {
            throw new ApiParamsError('参数错误');
        }
        $authcode = strval($authcode);
        $admin_id = AdminUser::checkItem($name, 'name');
        if (empty($admin_id)) {
            throw new ApiParamsError('该用户不存在');
        }

        $sms_info = $this->_getAdminAuthCode($admin_id, $type);
        $sms_info['last_sms_time'] = Util::v($sms_info, 'last_sms_time', time());

        if (time() - $sms_info['last_sms_time'] > self::AUTHCODE_EXPIRE) {  //验证码 有效期 默认十分钟
            throw new ApiParamsError('验证码已过期');
        }
        if ($sms_info['auth_code'] != $authcode) {
            throw new ApiParamsError('验证码错误');
        }

        $rst = ['msg' => '验证码正确'];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 设置新的密码
     * @param string $name 登陆账号 必选
     * @param string $type 发送方式 必选 目前支持 telephone 手机短信  email 邮件
     * @param string $authcode 短信 或者 邮件验证码 必选
     * @param string $new_pasw 新密码 6 - 18  位  preg_match("/^.{6,18}$/i", $password)
     * @return array ['msg' => '重置密码成功',]
     * @throws ApiParamsError
     */
    public function setPwdByAuthCode($name, $type, $authcode, $new_pasw)
    {
        $this->checkAuthCode($name, $type, $authcode);

        if (!self::_validPassWord($new_pasw)) {
            throw new ApiParamsError('密码格式错误');
        }
        $admin_id = AdminUser::checkItem($name, 'name');
        if (empty($admin_id)) {
            throw new ApiParamsError('该用户不存在');
        }
        $this->_clearAdminAuthCode($admin_id, $type);

        AdminUser::setOneById($admin_id, [
            'pasw' => $new_pasw,
        ]);
        $ip = $this->client_ip();
        $op_desc = '用户重置密码';
        AdminRecord::createOne([
            'room_id' => 0,    //  INTEGER  操作相关  room_id  尽可能 尝试记录
            'admin_id' => $admin_id,   //  INTEGER  操作者  admin_id  尽可能 尝试记录
            'op_type' => 3,    //  SMALLINT  操作类型  0 未知  1 登录  2 登出 3 其他操作
            'op_desc' => $op_desc,    //  VARCHAR(255)  field op_desc
            'op_ref' => $this->getRequest()->getHttpReferer(),    //  VARCHAR(255)  field op_ref
            'op_url' => $this->fullUrl(),    //  VARCHAR(255)  field op_url
            'op_args' => self::funcGetArgs(func_get_args(), __METHOD__),    //  TEXT  本次操作的 参数
            'op_ip' => $ip,    //  VARCHAR(20)  field op_ip
            'op_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
            'op_method' => __METHOD__,  // VARCHAR(255)  field op_method
            'op_admin_id' => $admin_id,    //  INTEGER  操作者  admin_id  尽可能 尝试记录
        ]);

        $rst = ['msg' => '重置密码成功'];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    ################################################################
    ###########################  设置密码 API ##########################
    ################################################################

    /**
     * 验证自己的密码  需要当前密码
     * @param string $pasw 登录密码
     * @return array ['msg' => '验证密码成功'];
     * @throws ApiParamsError
     * @throws ApiAuthError
     */
    public function testSelfPaswAdmin($pasw)
    {
        $admin_id = $this->auth()->id();
        if ($admin_id <= 0) {
            throw new ApiAuthError('未登录');
        }
        $pasw = trim($pasw);
        if (!self::_validPassWord($pasw)) {
            throw new ApiParamsError('密码格式错误');
        }
        if (!AdminUser::testAdminPwd(AdminUser::name($admin_id), $pasw)) {
            throw new ApiParamsError('原密码错误');
        }

        $ip = $this->client_ip();
        $op_desc = '用户验证密码';
        AdminRecord::createOne([
            'room_id' => 0,    //  INTEGER  操作相关  room_id  尽可能 尝试记录
            'admin_id' => $admin_id,   //  INTEGER  操作者  admin_id  尽可能 尝试记录
            'op_type' => 1,    //  SMALLINT  操作类型  0 未知  1 登录  2 登出 3 其他操作
            'op_desc' => $op_desc,    //  VARCHAR(255)  field op_desc
            'op_ref' => $this->getRequest()->getHttpReferer(),    //  VARCHAR(255)  field op_ref
            'op_url' => $this->fullUrl(),    //  VARCHAR(255)  field op_url
            'op_args' => self::funcGetArgs(func_get_args(), __METHOD__),    //  TEXT  本次操作的 参数
            'op_ip' => $ip,    //  VARCHAR(20)  field op_ip
            'op_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
            'op_method' => __METHOD__,  // VARCHAR(255)  field op_method
            'op_admin_id' => $admin_id,    //  INTEGER  操作者  admin_id  尽可能 尝试记录
        ]);

        $rst = ['msg' => '验证密码成功'];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 设置自己的密码  需要原密码
     * @param string $old_pasw 原登录密码
     * @param string $new_pasw 新登录密码
     * @return array ['msg' => '修改密码成功'];
     * @throws ApiParamsError
     * @throws ApiAuthError
     */
    public function setSelfPaswAdmin($old_pasw, $new_pasw)
    {
        $admin_id = $this->auth()->id();
        if ($admin_id <= 0) {
            throw new ApiAuthError('未登录');
        }
        $old_pasw = trim($old_pasw);
        $new_pasw = trim($new_pasw);
        if (!self::_validPassWord($new_pasw)) {
            throw new ApiParamsError('密码格式错误');
        }
        if (!AdminUser::testAdminPwd(AdminUser::name($admin_id), $old_pasw)) {
            throw new ApiParamsError('原密码错误');
        }
        if (Util::str_cmp($new_pasw, $old_pasw)) {
            throw new ApiParamsError('新密码同旧密码相同');
        }

        AdminUser::setOneById($admin_id, [
            'pasw' => $new_pasw,
        ]);
        $ip = $this->client_ip();
        $op_desc = '用户修改密码';
        AdminRecord::createOne([
            'room_id' => 0,    //  INTEGER  操作相关  room_id  尽可能 尝试记录
            'admin_id' => $admin_id,   //  INTEGER  操作者  admin_id  尽可能 尝试记录
            'op_type' => 3,    //  SMALLINT  操作类型  0 未知  1 登录  2 登出 3 其他操作
            'op_desc' => $op_desc,    //  VARCHAR(255)  field op_desc
            'op_ref' => $this->getRequest()->getHttpReferer(),    //  VARCHAR(255)  field op_ref
            'op_url' => $this->fullUrl(),    //  VARCHAR(255)  field op_url
            'op_args' => self::funcGetArgs(func_get_args(), __METHOD__),    //  TEXT  本次操作的 参数
            'op_ip' => $ip,    //  VARCHAR(20)  field op_ip
            'op_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
            'op_method' => __METHOD__,  // VARCHAR(255)  field op_method
            'op_admin_id' => $admin_id,    //  INTEGER  操作者  admin_id  尽可能 尝试记录
        ]);

        $rst = ['msg' => '修改密码成功'];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    ################################################################
    ###########################  登录 API ##########################
    ################################################################

    /**
     * 账号密码登陆
     * @param string $name
     * @param string $pasw
     * @param int $remember
     * @return array ['msg' => '登陆成功'];
     * @throws ApiAuthError
     * @throws ApiParamsError
     */
    public function sessionLoginByPwd($name = '', $pasw = '', $remember = 1)
    {
        $time_str = date('Y-m-d H:i:s');
        $remember = !empty($remember);
        $name = trim($name);
        $pasw = trim($pasw);

        if ($name === '' || $pasw === '' || Util::utf8_strlen($name) > 32 || Util::utf8_strlen($pasw) > 32) {
            throw new ApiParamsError('请输入帐号密码');
        }

        $credentials = [
            'name' => $name,
            'pasw' => $pasw,
        ];

        if (!$this->auth()->attempt($credentials, false, false)) {
            throw new ApiAuthError('账号或密码错误');
        }
        $admin = $this->auth()->getLastAttempted();
        if (empty($admin) || empty($admin->admin_id)) {
            throw new ApiAuthError('用户不存在');
        }

        $admin_id = $admin->admin_id;
        self::_D(AdminUser::state($admin_id), '$admin_id');
        if (!AdminUser::testAdminState($admin_id)) {  // 检查用户状态
            throw new ApiAuthError('用户被冻结');
        }
        $this->auth()->loginUsingId($admin_id, $remember);

        $ip = $this->client_ip();
        $op_desc = '用户登入后台';
        AdminUser::incOneById($admin_id, 'login_count', 1, [
            'login_ip' => $ip,    //  VARCHAR(32)  上次登录ip
            'login_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
            'login_time' => $time_str,    //  DATETIME  上次登陆时间
            'last_msg' => $op_desc,    //  VARCHAR(256)  上次操作附加信息
        ]);   //  INTEGER  登录次数

        AdminRecord::createOne([
            'room_id' => 0,    //  INTEGER  操作相关  room_id  尽可能 尝试记录
            'admin_id' => $admin_id,   //  INTEGER  操作者  admin_id  尽可能 尝试记录
            'op_type' => 1,    //  SMALLINT  操作类型  0 未知  1 登录  2 登出 3 其他操作
            'op_desc' => $op_desc,    //  VARCHAR(255)  field op_desc
            'op_ref' => $this->getRequest()->getHttpReferer(),    //  VARCHAR(255)  field op_ref
            'op_url' => $this->fullUrl(),    //  VARCHAR(255)  field op_url
            'op_args' => self::funcGetArgs(func_get_args(), __METHOD__),    //  TEXT  本次操作的 参数
            'op_ip' => $ip,    //  VARCHAR(20)  field op_ip
            'op_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
            'op_method' => __METHOD__,  // VARCHAR(255)  field op_method
            'op_admin_id' => $admin_id,    //  INTEGER  操作者  admin_id  尽可能 尝试记录
        ]);

        $rst = ['msg' => '登陆成功'];
        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

    /**
     * 登出当前用户
     * @return array ['msg' => '登出成功'];
     */
    public function sessionLogout()
    {
        $admin_id = $this->auth()->id();
        $ip = $this->client_ip();
        $op_desc = '用户登出后台';
        $this->auth()->logout();

        if (!empty($admin_id)) {
            AdminRecord::createOne([
                'room_id' => 0,    //  INTEGER  操作相关  room_id  尽可能 尝试记录
                'admin_id' => $admin_id,   //  INTEGER  操作者  admin_id  尽可能 尝试记录
                'op_type' => 2,    //  SMALLINT  操作类型  0 未知  1 登录  2 登出 3 其他操作
                'op_desc' => $op_desc,    //  VARCHAR(255)  field op_desc
                'op_ref' => $this->getRequest()->getHttpReferer(),    //  VARCHAR(255)  field op_ref
                'op_url' => $this->fullUrl(),    //  VARCHAR(255)  field op_url
                'op_args' => self::funcGetArgs(func_get_args(), __METHOD__),    //  TEXT  本次操作的 参数
                'op_ip' => $ip,    //  VARCHAR(20)  field op_ip
                'op_location' => Util::getIpLocation($ip),    //  VARCHAR(32)  field op_location
                'op_method' => __METHOD__,  // VARCHAR(255)  field op_method
                'op_admin_id' => $admin_id,    //  INTEGER  操作者  admin_id  尽可能 尝试记录
            ]);
        }

        $rst = ['msg' => '登出成功'];
        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }
}