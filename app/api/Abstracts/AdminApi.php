<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/10/25
 * Time: 15:41
 */

namespace app\api\Abstracts;


use app\Exception\ApiAuthError;
use app\Libs\MgrAuth;
use app\Model\AdminUser;
use app\Model\SiteMgrUser;
use app\Request;
use app\Response;

abstract class AdminApi extends Api
{

    protected $admin_id = 0;

    /**
     * @return static
     */
    public static function createApiAsSuper()
    {
        $op_admin_id = self::_tryFindFirstSuperId();
        $api = (new static(new Request(), new Response()))->_hookAuthAdminId($op_admin_id);
        return $api;
    }

    public function _hookAuthAdminId($admin_id)
    {
        $admin_id = intval($admin_id);
        $this->admin_id = $admin_id;
        $this->auth()->onceUsingId($admin_id);
        return $this;
    }

    public function _getAuthAdminId()
    {
        return $this->admin_id;
    }

    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);

        $mgr_id = (new MgrAuth($this))->id();
        if (SiteMgrUser::testMgrState($mgr_id) && SiteMgrUser::checkOne($mgr_id, 'mgr_slug', SiteMgrUser::SLUG_SITE_ACCOUNTANT)) {  // 财务账号 hook 为 超管权限
            $admin_id = self::_tryFindFirstSuperId();
            $this->_hookAuthAdminId($admin_id);
        }

        $this->admin_id = intval($this->auth()->id());
        if ($this->admin_id <= 0 || !AdminUser::testAdminState($this->admin_id, 1)) {
            // 检测用户不存在  或者 用户被冻结
            throw new ApiAuthError("AdminApi AdminUser::testAdminState Error admin_id:{$this->admin_id}");
        }

        return $params;
    }

}