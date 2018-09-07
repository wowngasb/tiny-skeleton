# coding: utf-8
import random
import time
import hashlib
from inspect import isclass

from sqlalchemy.inspection import inspect as sqlalchemyinspect
from sqlalchemy.ext.declarative import declarative_base

from pykl.tiny.grapheneinfo import (
    _is_graphql,
    _is_graphql_cls,
    _is_graphql_mutation
)

from pykl.tiny.codegen.utils import (
    name_from_repr,
    camel_to_underline,
    underline_to_camel,
)

from base_type import *
from app import db, app
Base = db.Model


from player_base import (
    PlayerBase,
    PlayerBasePagination,
    PlayerMps,
    PlayerAodian,
    PlayerAli,
    PlayerUnion,
)

from stream_base import (
    StreamBase,
    StreamBasePagination,
    StreamMcs,
    StreamVod,
    StreamVodPagination,
    StreamPull,
    StreamUnion,
)

from admin_base import AdminUser, AdminUserPagination

from admin_other import (
    AdminCountly,
    AdminAccessControl,
    AdminRecord,
    AdminRecordPagination,
)

from daily_base import (
    DailyRoomRunning,
    DailyRoomRunningPagination,
    DailyRoomRunningDms,
    DailyRoomRunningDmsPagination,
    DailyViewCount,
    DailyViewCountPagination,
)

from room_base import (
    LiveRoom,
    LiveRoomPagination,
)

from room_other import (
    RoomContentConfig,
    RoomContentConfigPagination,
    RoomPublishRecord,
    RoomPublishRecordPagination,
)

from running_base import (
    RoomRunning,
    RoomRunningPagination,
    RoomRunningSum,
    RoomRunningSumPagination,
    RoomRunningDms,
    RoomRunningDmsPagination,
    RoomRunningDmsRef,
    RoomRunningDmsRefPagination,
    RoomRunningDmsSum,
    RoomRunningDmsSumPagination,
)

from site_base import (
    SiteMgrUser,
    SiteMgrUserPagination,
    SiteOpRecord,
    ArticleClassify,
    ArticleList,
    ArticleListPagination,
    HelpDocList,
    SendLog,
    SendLogPagination,
)

from viewrecord_base import (
    RoomViewRecord,
    RoomViewRecordPagination,
    RoomViewRecordDms,
    RoomViewRecordDmsPagination,
)

from xdy_base import (
    XdyProduct,
    XdyProductPagination,
    XdyOrder,
    XdyOrderPagination,
    XdyAdminProduct,
    XdyAdminProductPagination,
)


def md5(s):
    m2 = hashlib.md5()
    m2.update(s)
    return m2.hexdigest()

def md5key(pwd):
    key = app.config.get('PHP_CONFIG', {}).get('ENV_CRYPT_KEY', '')
    tmp = md5(key + pwd)
    return md5(pwd + tmp)


##############################################################
###################		根查询 Query		######################
##############################################################

class Query(g.ObjectType):
    hello = g.String(name=g.Argument(g.String, default_value="world", description=u'input you name'))
    deprecatedField = Field(g.String, deprecation_reason = 'This field is deprecated!')
    fieldWithException = g.String()

    def resolve_hello(self, args, context, info):
        return 'Hello, %s!' % (args.get('name', ''), )

    def resolve_deprecatedField(self, args, context, info):
        return 'You can request deprecated field, but it is not displayed in auto-generated documentation by default.'

    def resolve_fieldWithException(self, args, context, info):
        raise ValueError('Exception message thrown in field resolver')

    curAdmin = Field(AdminUser, description=u'当前登陆的后台账号')
    def resolve_curAdmin(self, args, context, info):
        return AdminUser.query.filter_by().first()

    roomContent = Field(RoomContentConfig, description=u'根据 content_id 查询 配置 信息',
        content_id=g.Argument(g.Int, required=True, description=u'配置 content_id')
    )
    def resolve_roomContent(self, args, context, info):
        content_id = args.get('content_id', 0)
        return RoomContentConfig.query.filter_by(content_id=content_id).first()

    sendLogList = Field(SendLogPagination, description=u'查询 sendLog 列表 需要超管权限',
        num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
        page=g.Argument(g.Int, default_value=1, description=u'页数'),

        admin_id=g.Argument(g.Int, default_value=0, description=u'检索 admin_id'),
		business_belong=g.Argument(g.Int, default_value=0, description=u'检索 business_belong'),
		send_id=g.Argument(g.Int, default_value=0, description=u'检索 send_id'),

        sender_addr=g.Argument(g.String, default_value='', description=u'模糊检索 sender_addr'),
        sender_type=g.Argument(g.String, default_value='', description=u'模糊检索 sender_type'),
        sender_msg=g.Argument(g.String, default_value='', description=u'检索 sender_msg'),

        created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
        updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
        sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
    )
    def resolve_sendLogList(self, args, context, info):
        page = args.get('page', 1)
        num = args.get('num', 20)
        query = sendLog.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
        rows = query.skip((page - 1)*num).limit(num).all()
        total = query.count()
        pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(SiteMgrUser, SortableField))
        return SendLogPagination(rows=rows, pageInfo=pageInfo)


    siteMgr = Field(SiteMgrUser, description=u'根据 mgr_id 查询 管理员 信息',
        mgr_id=g.Argument(g.Int, required=True, description=u'分类 mgr_id')
    )
    def resolve_siteMgr(self, args, context, info):
        mgr_id = args.get('mgr_id', 0)
        return SiteMgrUser.query.filter_by(mgr_id=mgr_id).first()

    siteMgrList = Field(lambda :SiteMgrUserPagination, description=u'查询 site 管理员列表 需要超管权限',
        num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
        page=g.Argument(g.Int, default_value=1, description=u'页数'),
        mgr_id=g.Argument(g.Int, default_value=0, description=u'检索 mgr_id'),
        name=g.Argument(g.String, default_value='', description=u'模糊检索 name'),
        title=g.Argument(g.String, default_value='', description=u'模糊检索 title'),
        mgr_slug=g.Argument(g.String, default_value='', description=u'检索 mgr_slug'),
        created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
        updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
        state=g.Argument(StateEnum, default_value=0, description=u'检索 状态枚举值'),
        sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
    )
    def resolve_siteMgrList(self, args, context, info):
        page = args.get('page', 1)
        num = args.get('num', 20)
        query = SiteMgrUser.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
        rows = query.skip((page - 1)*num).limit(num).all()
        total = query.count()
        pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(SiteMgrUser, SortableField))
        return SiteMgrUserPagination(rows=rows, pageInfo=pageInfo)

    superAdminList = Field(lambda :AdminUserPagination, description=u'查询 super 管理员列表 需要超管权限',
        num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
        page=g.Argument(g.Int, default_value=1, description=u'页数'),
        admin_id=g.Argument(g.Int, default_value=0, description=u'检索 admin_id'),
        name=g.Argument(g.String, default_value='', description=u'模糊检索 name'),
        title=g.Argument(g.String, default_value='', description=u'模糊检索 title'),
        admin_slug=g.Argument(g.String, default_value='', description=u'检索 admin_slug'),
        created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
        updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
        state=g.Argument(StateEnum, default_value=0, description=u'检索 状态枚举值'),
        sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
    )
    def resolve_superAdminList(self, args, context, info):
        page = args.get('page', 1)
        num = args.get('num', 20)
        query = AdminUser.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
        rows = query.skip((page - 1)*num).limit(num).all()
        total = query.count()
        pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(AdminUser, SortableField))
        return AdminUserPagination(rows=rows, pageInfo=pageInfo)

    classifyItem = Field(ArticleClassify, description=u'根据 classify_id 查询分类信息',
        classify_id=g.Argument(g.Int, required=True, description=u'分类 classify_id')
    )
    def resolve_classifyItem(self, args, context, info):
        classify_id = args.get('classify_id', 0)
        return ArticleClassify.query.filter_by(classify_id=classify_id).first()

    classifyList = List(ArticleClassify, description=u'查询所有 分类 列表')
    def resolve_classifyList(self, args, context, info):
        return ArticleClassify.query.all()

    articleItem = Field(ArticleList, description=u'根据 article_id 查询文章信息',
        article_id=g.Argument(g.Int, required=True, description=u'文章 article_id')
    )
    def resolve_articleItem(self, args, context, info):
        article_id = args.get('article_id', 0)
        return ArticleList.query.filter_by(article_id=article_id).first()

    artList = List(ArticleList, description=u'根据 classify_id 查询文章列表',
        classify_id=g.Argument(g.Int, required=True, description=u'分类 classify_id')
    )
    def resolve_artList(self, args, context, info):
        classify_id = args.get('classify_id', 0)
        return ArticleList.query.filter_by(article_id=article_id).all()

    opItem = Field(SiteOpRecord, description=u'根据 id 查询操作记录',
        id=g.Argument(g.Int, required=True, description=u'操作记录 id')
    )
    def resolve_opItem(self, args, context, info):
        id = args.get('id', 0)
        return SiteOpRecord.query.filter_by(id=id).first()

    helpList = List(HelpDocList, description=u'查询所有 帮助 列表')
    def resolve_helpList(self, args, context, info):
        return HelpDocList.query.all()

    admin = Field(AdminUser, description=u'根据admin_id 查询后台帐号信息',
        admin_id=g.Argument(g.Int, required=True, description=u'后台用户 admin_id')
    )
    def resolve_admin(self, args, context, info):
        admin_id = args.get('admin_id', 0)
        return AdminUser.query.filter_by(admin_id=admin_id).first()

    room = Field(LiveRoom, description=u'根据room_id 查询频道信息',
        room_id=g.Argument(g.Int, required=True, description=u'频道 room_id')
    )
    def resolve_room(self, args, context, info):
        room_id = args.get('room_id', 0)
        return LiveRoom.query.filter_by(room_id=room_id).first()

    player = Field(PlayerBase, description=u'根据player_id 查询播放器信息',
        player_id=g.Argument(g.Int, required=True, description=u'播放器 player_id')
    )
    def resolve_player(self, args, context, info):
        player_id = args.get('player_id', 0)
        return PlayerBase.query.filter_by(player_id=player_id).first()

    stream = Field(StreamBase, description=u'根据stream_id 查询视频流信息',
        stream_id=g.Argument(g.Int, required=True, description=u'视频流 stream_id')
    )
    def resolve_stream(self, args, context, info):
        stream_id = args.get('player_id', 0)
        return StreamBase.query.filter_by(stream_id=stream_id).first()


##############################################################
###################		 Mutations		######################
##############################################################
def build_input(dao, bit_mask):
    return {k: BuildArgument(v) for k, v in mask_field(dao, bit_mask).items()}

######################## 管理员 相关 ############################

class CreateAdminUser(g.Mutation):
    Input = type('Input', (), build_input(AdminUser, InitializeField))

    ok = g.Boolean()
    msg = g.String()
    admin = Field(AdminUser)

    @staticmethod
    def mutate(root, args, context, info):
        pass

class UpdateAdminUser(g.Mutation):
    Input = type('Input', (), build_input(AdminUser, EditableField))

    ok = g.Boolean()
    msg = g.String()
    admin = Field(AdminUser)

    @staticmethod
    def mutate(root, args, context, info):
        pass

######################## 播放器 相关 ############################

class CreatePlayerAodian(g.Mutation):
    Input = type('Input', (), build_input(PlayerAodian, InitializeField))

    ok = g.Boolean()
    msg = g.String()
    player = Field(PlayerBase)

    @staticmethod
    def mutate(root, args, context, info):
        pass

class UpdatePlayerAodian(g.Mutation):
    Input = type('Input', (), build_input(PlayerAodian, EditableField))

    ok = g.Boolean()
    msg = g.String()
    player = Field(PlayerBase)

    @staticmethod
    def mutate(root, args, context, info):
        pass

class CreatePlayerMps(g.Mutation):
    Input = type('Input', (), build_input(PlayerMps, InitializeField))

    ok = g.Boolean()
    msg = g.String()
    player = Field(PlayerBase)

    @staticmethod
    def mutate(root, args, context, info):
        pass

class UpdatePlayerMps(g.Mutation):
    Input = type('Input', (), build_input(PlayerMps, EditableField))

    ok = g.Boolean()
    msg = g.String()
    player = Field(PlayerBase)

    @staticmethod
    def mutate(root, args, context, info):
        pass

class CreatePlayerAli(g.Mutation):
    Input = type('Input', (), build_input(PlayerAli, InitializeField))

    ok = g.Boolean()
    msg = g.String()
    player = Field(PlayerBase)

    @staticmethod
    def mutate(root, args, context, info):
        pass

class UpdatePlayerAli(g.Mutation):
    Input = type('Input', (), build_input(PlayerAli, EditableField))

    ok = g.Boolean()
    msg = g.String()
    player = Field(PlayerBase)

    @staticmethod
    def mutate(root, args, context, info):
        pass

##############################################################
###################		根查询 Mutations		######################
##############################################################

Mutations = type('Mutations', (g.ObjectType, ), {camel_to_underline(name_from_repr(v)):v.Field() for _, v in globals().items() if _is_graphql_mutation(v)})

tables = [tbl if BuildType(tbl) else tbl for _, tbl in globals().items() if isclass(tbl) and issubclass(tbl, Base) and tbl != Base]
schema = g.Schema(query=Query, mutation=Mutations, types=[BuildType(tbl) for tbl in tables] + [cls for _, cls in globals().items() if _is_graphql_cls(cls)], auto_camelcase = False)