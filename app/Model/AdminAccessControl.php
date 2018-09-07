<?php
/**
 * Created by table_graphQL.
 * 用于PHP Tiny框架
 * Date: 2018-05
 */

namespace app\Model;

use app\Model_\AdminAccessControl_;

/**
 * Class AdminAccessControl
 * 子公司 旗下子账号 权限设置 访问控制表 每条记录为一项权限
 * 数据表 admin_access_control
 * @package app\Model
 */
class AdminAccessControl extends AdminAccessControl_
{

    ####################################
    ############# 改写代码 ##############
    ####################################

    const ACCESS_TYPE_MENU = 'menu';

    const ACCESS_TYPE_ROOM = 'room';

    const ACCESS_TYPE_PAGETAB = 'pagetab';

}