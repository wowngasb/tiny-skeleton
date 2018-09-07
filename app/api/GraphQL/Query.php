<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/3/2 0002
 * Time: 16:02
 */

namespace app\api\GraphQL;


use app\api\Abstracts\AbstractApi;
use app\api\Abstracts\Api;
use app\api\GraphQL\ExtType\PageInfo;
use app\api\GraphQL_\AbstractQuery;
use app\api\GraphQL_\Enum\AdminTypeEnum;
use app\api\GraphQL_\Enum\StateEnum;
use app\Exception\ApiAuthBeyondError;
use app\Exception\ApiAuthError;
use app\Model\AdminUser as _AdminUser;
use app\Model\ArticleClassify as _ArticleClassify;
use app\Model\ArticleList as _ArticleList;
use app\Model\HelpDocList as _HelpDocList;
use app\Model\LiveRoom as _LiveRoom;
use app\Model\PlayerBase as _PlayerBase;
use app\Model\RoomContentConfig as _RoomContentConfig;
use app\Model\SendLog as _SendLog;
use app\Model\SiteMgrUser as _SiteMgrUser;
use app\Model\SiteOpRecord as _TableOpRecord;
use app\Model\StreamBase as _StreamBase;
use app\Util;
use GraphQL\Type\Definition\ResolveInfo;
use Tiny\OrmQuery\Q;

class Query extends AbstractQuery
{

    static $max_page_num = 200;

    static $max_csv_num = 2 * 10000;

    /**
     * 当前登陆的后台账号
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info = null
     * @return mixed AdminUser
     * @throws ApiAuthError
     */
    public function curAdmin($rootValue, $args, $context, ResolveInfo $info = null)
    {
        /** @var AbstractApi $context */
        $cur_admin_id = $context->auth()->id();
        if (!_AdminUser::testAdminState($cur_admin_id)) {
            throw new ApiAuthError("Query curAdmin Error cur_admin_id:{$cur_admin_id}");
        }
        return _AdminUser::getOneById($cur_admin_id);
    }

    /**
     * 根据admin_id 查询后台帐号信息
     * _param Int $args['admin_id'] 后台用户 admin_id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info = null
     * @return mixed AdminUser
     * @throws ApiAuthBeyondError
     */
    public function admin($rootValue, $args, $context, ResolveInfo $info = null)
    {
        $admin_id = isset($args['admin_id']) ? intval($args['admin_id']) : 0;    //  Int  后台用户 admin_id (NonNull)
        if (!_AdminUser::checkOne($admin_id)) {
            return null;
        }
        /** @var AbstractApi $context */
        $cur_admin_id = $context->auth()->id();
        Api::_checkBeyondAdmin($cur_admin_id, $admin_id, __METHOD__ . " Error cur_admin_id:{$cur_admin_id}, admin_id:{$admin_id}");
        return _AdminUser::getOneById($admin_id);
    }

    /**
     * 根据room_id 查询频道信息
     * _param Int $args['room_id'] 频道 room_id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info = null
     * @return mixed LiveRoom
     * @throws ApiAuthBeyondError
     */
    public function room($rootValue, $args, $context, ResolveInfo $info = null)
    {
        $room_id = isset($args['room_id']) ? intval($args['room_id']) : 0;    //  Int  频道 room_id (NonNull)
        if (!_LiveRoom::checkOne($room_id)) {
            return null;
        }
        /** @var AbstractApi $context */
        $cur_admin_id = $context->auth()->id();
        if (!Api::_checkBeyondRoom($cur_admin_id, $room_id)) {
            throw new ApiAuthBeyondError("Query room Error cur_admin_id:{$cur_admin_id}, room_id:{$room_id}");
        }
        return _LiveRoom::getOneById($room_id);
    }

    /**
     * 根据player_id 查询播放器信息
     * _param Int $args['player_id'] 播放器 player_id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info = null
     * @return mixed PlayerBase
     * @throws ApiAuthBeyondError
     */
    public function player($rootValue, $args, $context, ResolveInfo $info = null)
    {
        $player_id = isset($args['player_id']) ? intval($args['player_id']) : 0;    //  Int  播放器 player_id (NonNull)
        if (!_PlayerBase::checkOne($player_id)) {
            return null;
        }
        /** @var AbstractApi $context */
        $cur_admin_id = $context->auth()->id();
        if (!Api::_checkBeyondPlayer($cur_admin_id, $player_id)) {
            throw new ApiAuthBeyondError("Query player Error cur_admin_id:{$cur_admin_id}, player_id:{$player_id}");
        }
        return _PlayerBase::getOneById($player_id);
    }

    /**
     * 根据stream_id 查询视频流信息
     * _param Int $args['stream_id'] 视频流 stream_id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info = null
     * @return mixed StreamBase
     * @throws ApiAuthBeyondError
     */
    public function stream($rootValue, $args, $context, ResolveInfo $info = null)
    {
        $stream_id = isset($args['stream_id']) ? intval($args['stream_id']) : 0;    //  Int  视频流 stream_id (NonNull)
        if (!_StreamBase::checkOne($stream_id)) {
            return null;
        }
        /** @var AbstractApi $context */
        $cur_admin_id = $context->auth()->id();
        if (!Api::_checkBeyondStream($cur_admin_id, $stream_id)) {
            throw new ApiAuthBeyondError("Query stream Error cur_admin_id:{$cur_admin_id}, stream_id:{$stream_id}");
        }
        return _StreamBase::getOneById($stream_id);
    }

    /**
     * 根据 classify_id 查询分类信息
     * _param Int $args['classify_id'] 分类 classify_id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info = null
     * @return mixed ArticleClassify
     * @throws ApiAuthBeyondError
     */
    public function classifyItem($rootValue, $args, $context, ResolveInfo $info = null)
    {
        $classify_id = isset($args['classify_id']) ? intval($args['classify_id']) : 0;    //  Int $args['classify_id'] 分类 classify_id (NonNull)
        if (!_ArticleClassify::checkOne($classify_id)) {
            return null;
        }
        /** @var AbstractApi $context */
        $cur_admin_id = $context->auth()->id();
        if (!Api::_isSuper($cur_admin_id)) {
            throw new ApiAuthBeyondError("Query classifyItem Error cur_admin_id:{$cur_admin_id}, classify_id:{$classify_id}");
        }
        return _ArticleClassify::getOneById($classify_id);
    }

    /**
     * 根据 article_id 查询文章信息
     * _param Int $args['article_id'] 文章 article_id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info = null
     * @return mixed ArticleList
     * @throws ApiAuthBeyondError
     */
    public function articleItem($rootValue, $args, $context, ResolveInfo $info = null)
    {
        $article_id = isset($args['article_id']) ? intval($args['article_id']) : 0;    //  Int $args['article_id'] 文章 article_id (NonNull)
        if (!_ArticleList::checkOne($article_id)) {
            return null;
        }
        /** @var AbstractApi $context */
        $cur_admin_id = $context->auth()->id();
        if (!Api::_isSuper($cur_admin_id)) {
            throw new ApiAuthBeyondError("Query articleItem Error cur_admin_id:{$cur_admin_id}, article_id:{$article_id}");
        }
        return _ArticleList::getOneById($article_id);
    }

    /**
     * 根据 id 查询操作记录
     * _param Int $args['id'] 操作记录 id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info = null
     * @return mixed TableOpRecord
     * @throws ApiAuthBeyondError
     */
    public function opItem($rootValue, $args, $context, ResolveInfo $info = null)
    {
        $id = isset($args['id']) ? intval($args['id']) : 0;    //  Int $args['id'] 操作记录 id (NonNull)
        if (!_TableOpRecord::checkOne($id)) {
            return null;
        }
        /** @var AbstractApi $context */
        $cur_admin_id = $context->auth()->id();
        if (!Api::_isSuper($cur_admin_id)) {
            throw new ApiAuthBeyondError("Query opItem Error cur_admin_id:{$cur_admin_id}, id:{$id}");
        }
        return _TableOpRecord::getOneById($id);
    }

    /**
     * 根据 content_id 查询 配置 信息
     * _param Int $args['content_id'] 配置 content_id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info = null
     * @return mixed RoomContentConfig
     * @throws ApiAuthBeyondError
     */
    public function roomContent($rootValue, $args, $context, ResolveInfo $info = null)
    {
        $content_id = isset($args['content_id']) ? intval($args['content_id']) : 0;    //  Int $args['content_id'] 配置 content_id (NonNull)
        if (!_RoomContentConfig::checkOne($content_id)) {
            return null;
        }
        /** @var AbstractApi $context */
        $cur_admin_id = $context->auth()->id();
        if (!Api::_isSuper($cur_admin_id)) {
            throw new ApiAuthBeyondError("Query roomContent Error cur_admin_id:{$cur_admin_id}, content_id:{$content_id}");
        }
        return _RoomContentConfig::getOneById($content_id);
    }

    /**
     * 根据 mgr_id 查询 管理员 信息
     * _param Int $args['mgr_id'] 分类 mgr_id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info = null
     * @return mixed SiteMgrUser
     * @throws ApiAuthBeyondError
     */
    public function siteMgr($rootValue, $args, $context, ResolveInfo $info = null)
    {
        $mgr_id = isset($args['mgr_id']) ? intval($args['mgr_id']) : 0;    //  Int $args['mgr_id'] 分类 mgr_id (NonNull)
        if (!_SiteMgrUser::checkOne($mgr_id)) {
            return null;
        }
        /** @var AbstractApi $context */
        $cur_admin_id = $context->auth()->id();
        if (!Api::_isSuper($cur_admin_id)) {
            throw new ApiAuthBeyondError("Query siteMgr Error cur_admin_id:{$cur_admin_id}, mgr_id:{$mgr_id}");
        }
        return _SiteMgrUser::getOneById($mgr_id);
    }

    /**
     * 查询 site 管理员列表 需要超管权限
     * _param Int $args['num'] = 20 每页数量
     * _param Int $args['page'] = 1 页数
     * _param Int $args['mgr_id'] = 0 检索 mgr_id
     * _param String $args['name'] = "" 模糊检索 name
     * _param String $args['title'] = "" 模糊检索 title
     * _param DateRange $args['created_at'] 范围检索 创建时间
     * _param DateRange $args['updated_at'] 范围检索 记录更新时间
     * _param StateEnum $args['state'] = 0 检索 状态枚举值
     * _param SortOption $args['sortOption'] 排序依据 为空将使用默认排序
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info = null
     * @return mixed SiteMgrUserPagination
     * @throws ApiAuthBeyondError
     */
    public function siteMgrList($rootValue, $args, $context, ResolveInfo $info = null)
    {
        /** @var AbstractApi $context */
        $cur_admin_id = $context->auth()->id();
        if (!Api::_isSuper($cur_admin_id)) {
            throw new ApiAuthBeyondError("Query siteMgrList Error cur_admin_id:{$cur_admin_id}");
        }

        $num = isset($args['num']) ? intval($args['num']) : 20;    //  Int  每页数量
        $page = isset($args['page']) ? intval($args['page']) : 1;    //  Int  页数
        $mgr_id = isset($args['mgr_id']) ? intval($args['mgr_id']) : 0;    //  Int  检索 mgr_id
        $name = isset($args['name']) ? strval($args['name']) : "";    //  String  模糊检索 name
        $title = isset($args['title']) ? strval($args['title']) : "";    //  String  模糊检索 title
        $mgr_slug = isset($args['mgr_slug']) ? strval($args['mgr_slug']) : "";    //  String  检索 mgr_slug
        $created_at = isset($args['created_at']) ? (array)$args['created_at'] : [];    //  DateRange  范围检索 创建时间
        $updated_at = isset($args['updated_at']) ? (array)$args['updated_at'] : [];    //  DateRange  范围检索 记录更新时间
        $state = isset($args['state']) ? $args['state'] : 0;    //  StateEnum  检索 状态枚举值
        $sortOption = isset($args['sortOption']) ? (array)$args['sortOption'] : [];    //  SortOption  排序依据 为空将使用默认排序

        $where = [
            'name' => Q::where("%{$name}%", 'like', function () use ($name) {
                return !empty($name);
            }),
            'title' => Q::where("%{$title}%", 'like', function () use ($title) {
                return !empty($title);
            }),
            'mgr_slug' => Q::where($mgr_slug, '=', function () use ($mgr_slug) {
                return !empty($mgr_slug);
            }),
            'mgr_id' => Q::where($mgr_id, '=', function () use ($mgr_id) {
                return $mgr_id > 0;
            }),
        ];
        $where = array_merge($where, Util::whereByState($state), Util::whereByCreatedAt($created_at), Util::whereByUpdatedAt($updated_at));

        $sortOption = Util::check_sort($sortOption, _SiteMgrUser::SORTABLE_FIELDS, _SiteMgrUser::PRIMARY_KEY, 'desc');
        $total = _SiteMgrUser::countItem($where);
        $offset = Util::page_offset($page, $num, $total, Query::$max_page_num);
        $list = $total > 0 ? _SiteMgrUser::selectItem($offset['offset'], $offset['num'], $sortOption, $where) : [];
        return PageInfo::buildPageInfoEx($list, $offset, $sortOption, _SiteMgrUser::SORTABLE_FIELDS);
    }

    /**
     * 查询 sendLog 列表 需要超管权限
     * _param Int $args['num'] = 20 每页数量
     * _param Int $args['page'] = 1 页数
     * _param Int $args['admin_id'] = 0 检索 admin_id
     * _param Int $args['business_belong'] = 0 检索 business_belong
     * _param Int $args['send_id'] = 0 检索 send_id
     * _param String $args['sender_addr'] = "" 模糊检索 sender_addr
     * _param String $args['sender_type'] = "" 模糊检索 sender_type
     * _param String $args['sender_msg'] = "" 检索 sender_msg
     * _param DateRange $args['created_at'] 范围检索 创建时间
     * _param DateRange $args['updated_at'] 范围检索 记录更新时间
     * _param StateEnum $args['state'] = 0 检索 状态枚举值
     * _param SortOption $args['sortOption'] 排序依据 为空将使用默认排序
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed SendLogPagination
     * @throws ApiAuthBeyondError
     */
    public function sendLogList($rootValue, $args, $context, ResolveInfo $info)
    {
        /** @var AbstractApi $context */
        $cur_admin_id = $context->auth()->id();
        if (!Api::_isSuper($cur_admin_id)) {
            throw new ApiAuthBeyondError("Query siteMgrList Error cur_admin_id:{$cur_admin_id}");
        }

        $num = isset($args['num']) ? intval($args['num']) : 20;    //  Int  每页数量
        $page = isset($args['page']) ? intval($args['page']) : 1;    //  Int  页数
        $admin_id = isset($args['admin_id']) ? intval($args['admin_id']) : 0;    //  Int  检索 admin_id
        $business_belong = isset($args['business_belong']) ? intval($args['business_belong']) : 0;    //  Int  检索 business_belong
        $send_id = isset($args['send_id']) ? intval($args['send_id']) : 0;    //  Int  检索 send_id
        $sender_addr = isset($args['sender_addr']) ? strval($args['sender_addr']) : "";    //  String  模糊检索 sender_addr
        $sender_type = isset($args['sender_type']) ? strval($args['sender_type']) : "";    //  String  模糊检索 sender_type
        $sender_msg = isset($args['sender_msg']) ? strval($args['sender_msg']) : "";    //  String  检索 sender_msg
        $created_at = isset($args['created_at']) ? (array)$args['created_at'] : [];    //  DateRange  范围检索 创建时间
        $updated_at = isset($args['updated_at']) ? (array)$args['updated_at'] : [];    //  DateRange  范围检索 记录更新时间
        $sortOption = isset($args['sortOption']) ? (array)$args['sortOption'] : [];    //  SortOption  排序依据 为空将使用默认排序

        $where = [
            'admin_id' => Q::where($admin_id, '=', function () use ($admin_id) {
                return $admin_id > 0;
            }),
            'business_belong' => Q::where($business_belong, '=', function () use ($business_belong) {
                return $business_belong > 0;
            }),
            'send_id' => Q::where($send_id, '=', function () use ($send_id) {
                return $send_id > 0;
            }),
            'sender_addr' => Q::where("%{$sender_addr}%", 'like', function () use ($sender_addr) {
                return !empty($sender_addr);
            }),
            'sender_type' => Q::where($sender_type, '=', function () use ($sender_type) {
                return !empty($sender_type);
            }),
            'sender_msg' => Q::where("%{$sender_msg}%", 'like', function () use ($sender_msg) {
                return !empty($sender_msg);
            }),
        ];
        $where = array_merge($where, Util::whereByCreatedAt($created_at), Util::whereByUpdatedAt($updated_at));

        $sortOption = Util::check_sort($sortOption, _SendLog::SORTABLE_FIELDS, _SendLog::PRIMARY_KEY, 'desc');
        $total = _SendLog::countItem($where);
        $offset = Util::page_offset($page, $num, $total, Query::$max_page_num);
        $list = $total > 0 ? _SendLog::selectItem($offset['offset'], $offset['num'], $sortOption, $where, ['*'], ['admin']) : [];
        return PageInfo::buildPageInfoEx($list, $offset, $sortOption, _SendLog::SORTABLE_FIELDS);
    }

    /**
     * 查询所有 分类 列表
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed ArticleClassify
     */
    public function classifyList($rootValue, $args, $context, ResolveInfo $info)
    {
        return _ArticleClassify::selectItem(0, 0, ['rank', 'desc'], [
            'state' => StateEnum::NORMAL_VALUE,
        ]);
    }

    /**
     * 根据 classify_id 查询文章列表
     * _param Int $args['classify_id'] 分类 classify_id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed ArticleList
     */
    public function artList($rootValue, $args, $context, ResolveInfo $info)
    {
        $classify_id = isset($args['classify_id']) ? intval($args['classify_id']) : 0;    //  Int  分类 classify_id (NonNull)
        return _ArticleList::selectItem(0, 0, ['rank', 'desc'], [
            'state' => StateEnum::NORMAL_VALUE,
            'classify_id' => Q::where($classify_id, '=', function () use ($classify_id) {
                return !empty($classify_id);
            })
        ]);
    }

    /**
     * 查询所有 帮助 列表
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed HelpDocList
     */
    public function helpList($rootValue, $args, $context, ResolveInfo $info)
    {
        return _HelpDocList::selectItem(0, 0, ['rank', 'desc'], [
            'state' => StateEnum::NORMAL_VALUE,
        ]);
    }

    /**
     * 查询 super 管理员列表 需要超管权限
     * _param Int $args['num'] = 20 每页数量
     * _param Int $args['page'] = 1 页数
     * _param Int $args['admin_id'] = 0 检索 admin_id
     * _param String $args['name'] = "" 模糊检索 name
     * _param String $args['title'] = "" 模糊检索 title
     * _param String $args['admin_slug'] = "" 检索 admin_slug
     * _param DateRange $args['created_at'] 范围检索 创建时间
     * _param DateRange $args['updated_at'] 范围检索 记录更新时间
     * _param StateEnum $args['state'] = 0 检索 状态枚举值
     * _param SortOption $args['sortOption'] 排序依据 为空将使用默认排序
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed AdminUserPagination
     */
    public function superAdminList($rootValue, $args, $context, ResolveInfo $info)
    {
        /** @var AbstractApi $context */
        $cur_admin_id = $context->auth()->id();
        if (!Api::_isSuper($cur_admin_id)) {
            throw new ApiAuthBeyondError("Query siteMgrList Error cur_admin_id:{$cur_admin_id}");
        }

        $num = isset($args['num']) ? intval($args['num']) : 20;    //  Int  每页数量
        $page = isset($args['page']) ? intval($args['page']) : 1;    //  Int  页数
        $admin_id = isset($args['admin_id']) ? intval($args['admin_id']) : 0;    //  Int  检索 admin_id
        $name = isset($args['name']) ? strval($args['name']) : "";    //  String  模糊检索 name
        $title = isset($args['title']) ? strval($args['title']) : "";    //  String  模糊检索 title
        $admin_slug = isset($args['admin_slug']) ? strval($args['admin_slug']) : "";    //  String  检索 admin_slug
        $created_at = isset($args['created_at']) ? (array)$args['created_at'] : [];    //  DateRange  范围检索 创建时间
        $updated_at = isset($args['updated_at']) ? (array)$args['updated_at'] : [];    //  DateRange  范围检索 记录更新时间
        $state = isset($args['state']) ? $args['state'] : 0;    //  StateEnum  检索 状态枚举值
        $sortOption = isset($args['sortOption']) ? (array)$args['sortOption'] : [];    //  SortOption  排序依据 为空将使用默认排序


        $where = [
            'admin_type' => AdminTypeEnum::SUPER_VALUE,
            'name' => Q::where("%{$name}%", 'like', function () use ($name) {
                return !empty($name);
            }),
            'title' => Q::where("%{$title}%", 'like', function () use ($title) {
                return !empty($title);
            }),
            'admin_slug' => Q::where($admin_slug, '=', function () use ($admin_slug) {
                return !empty($admin_slug);
            }),
            'admin_id' => Q::where($admin_id, '=', function () use ($admin_id) {
                return $admin_id > 0;
            }),
        ];
        $where = array_merge($where, Util::whereByState($state), Util::whereByCreatedAt($created_at), Util::whereByUpdatedAt($updated_at));

        $sortOption = Util::check_sort($sortOption, _AdminUser::SORTABLE_FIELDS, _AdminUser::PRIMARY_KEY, 'desc');
        $total = _AdminUser::countItem($where);
        $offset = Util::page_offset($page, $num, $total, Query::$max_page_num);
        $list = $total > 0 ? _AdminUser::selectItem($offset['offset'], $offset['num'], $sortOption, $where) : [];
        return PageInfo::buildPageInfoEx($list, $offset, $sortOption, _AdminUser::SORTABLE_FIELDS);

    }
}