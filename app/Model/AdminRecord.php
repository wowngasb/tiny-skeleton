<?php
/**
 * Created by table_graphQL.
 * 用于PHP Tiny框架
 * Date: 2018-05
 */

namespace app\Model;

use app\Model_\AdminRecord_;
use app\Util;

/**
 * Class AdminRecord
 * table admin_record
 * 数据表 admin_record
 * @package app\Model
 */
class AdminRecord extends AdminRecord_
{


    public static function newItem(array $data, $log_op = true)
    {
        if (!empty($data['op_ref'])) {
            $data['op_ref'] = Util::utf8_strlen($data['op_ref']) > 255 ? Util::utf8_substr($data['op_ref'], 0, 255) : $data['op_ref'];
        }
        if (!empty($data['op_url'])) {
            $data['op_url'] = Util::utf8_strlen($data['op_url']) > 255 ? Util::utf8_substr($data['op_url'], 0, 255) : $data['op_url'];
        }
        if (!empty($data['op_desc'])) {
            $data['op_desc'] = Util::utf8_strlen($data['op_desc']) > 255 ? Util::utf8_substr($data['op_desc'], 0, 255) : $data['op_desc'];
        }

        return parent::newItem($data, $log_op);
    }

    ####################################
    ############# 改写代码 ##############
    ####################################

    public function admin()
    {
        $item = $this->hasOne('app\Model\AdminUser', 'admin_id', 'admin_id');
        return $item;
    }

    public function op_admin()
    {
        $item = $this->hasOne('app\Model\AdminUser', 'admin_id', 'op_admin_id');
        return $item;
    }

    public function room()
    {
        $item = $this->hasOne('app\Model\LiveRoom', 'room_id', 'room_id');
        return $item;
    }

    public function stream()
    {
        $item = $this->hasOne('app\Model\StreamBase', 'stream_id', 'stream_id');
        return $item;
    }

    public function player()
    {
        $item = $this->hasOne('app\Model\PlayerBase', 'player_id', 'player_id');
        return $item;
    }
}