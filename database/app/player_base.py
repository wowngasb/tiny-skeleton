# coding: utf-8
from base_type import *
from app import db, app
Base = db.Model


##############################################################
####################		PlayerUnion		##################
##############################################################

class PlayerBase(Base):
    u"""播放器"""
    __tablename__ = 'player_base'
    player_id = Column(Integer, primary_key=True, doc=u"""自增主键""", info=SortableField)
    player_type = Column(String(32), nullable=False, index=True, doc=u"""播放器类型 参见 PlayerTypeEnum""", info=BitMask(InitializeField | SortableField, PlayerTypeEnum))
    player_name = Column(String(64), nullable=False, server_default=text("''"), doc=u"""播放器名称 用于标识""", info=CustomField)
    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""对应 parent 类型 后台用户admin_id""", info=InitializeField | SortableField)
    agent_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""对应 parent 类型 后台用户agent_id""", info=InitializeField | SortableField)
    state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""状态 参见 StateEnum 枚举""", info=BitMask(CustomField | SortableField, StateEnum))
    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class PlayerBase(SQLAlchemyObjectType):
            class Meta:
                model = cls

            player = Field(lambda :PlayerUnion, description=u'播放器 信息')
            def resolve_player(self, args, context, info):
                return _playerMap[self.player_type].query.filter_by(player_id=self.player_id).first() \
                        if self.player_id and self.player_type in _playerMap \
                            else None

            admin = Field(lambda :AdminUser, description=u'parent 类型 后台用户 信息')
            def resolve_admin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.admin_id).first() \
                        if self.admin_id \
                            else None

            agent = Field(lambda :AdminUser, description=u'agent 类型 后台用户 信息')
            def resolve_admin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.agent_id).first() \
                        if self.admin_id \
                            else None

        return PlayerBase

class PlayerBasePagination(g.ObjectType):
    u''' PlayerBase 分页查询 列表'''
    rows = List(PlayerBase, description=u'当前查询 PlayerBase 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')

class PlayerMps(Base):
    u"""MPS 播放器"""
    __tablename__ = 'player_mps'
    player_id = Column(Integer, primary_key=True, doc=u"""对应 PlayerBase player_id""", info=SortableField | InitializeField)

    # 需要配置流媒体相关参数 需要使用 MPS 动态模式
    # hlsUrl: ''  // hls地址
    # rtmpUrl: ''  // rtmp地址
    # flvUrl: ''  // flv地址
    width = Column(String(32), nullable=False, server_default=text("'100%'"), doc=u"""播放器宽度，可用数字、百分比等""", info=CustomField)
    height = Column(String(32), nullable=False, server_default=text("'100%'"), doc=u"""播放器高度，可用数字、百分比等""", info=CustomField)
    autostart = Column(SmallInteger, nullable=False, server_default=text("'0'"), doc=u""" 是否自动播放，默认为0 """, info=CustomField)
    controlbardisplay = Column(String(32), nullable=False, server_default=text("'disable'"), doc=u"""是否显示控制栏，值为：disable、enable  默认为disable""", info=CustomField)
    isclickplay = Column(SmallInteger, nullable=False, server_default=text("'0'"), doc=u""" 是否单击播放，默认为0 """, info=CustomField)
    isfullscreen = Column(SmallInteger, nullable=False, server_default=text("'1'"), doc=u""" 是否双击全屏，默认为1 """, info=CustomField)
    stretching = Column(SmallInteger, nullable=False, server_default=text("'1'"), doc=u""" 设置全屏模式,1代表按比例撑满至全屏,2代表铺满全屏,3代表视频原始大小,默认值为1 hls初始设置不支持，手机端不支持 默认为 1 """, info=CustomField)
    defvolume = Column(SmallInteger, nullable=False, server_default=text("'80'"), doc=u""" 默认音量，默认为80 """, info=CustomField)

    uin = Column(Integer, nullable=False, server_default=text("'0'"), doc=u"""aodian 用户uin""", info=CustomField)
    appId = Column(String(32), nullable=False, server_default=text("''"), doc=u"""播放实例ID""", info=CustomField)
    mobilefullscreen = Column(SmallInteger, nullable=False, server_default=text("'0'"), doc=u""" 移动端是否全屏，默认为0 """, info=CustomField)
    enablehtml5 = Column(SmallInteger, nullable=False, server_default=text("'0'"), doc=u""" 是否优先使用H5播放器，默认为0 """, info=CustomField)
    isloadcount = Column(SmallInteger, nullable=False, server_default=text("'1'"), doc=u""" 网络波动卡顿loading图标显示(默认1s后) """, info=CustomField)
    israte = Column(SmallInteger, nullable=False, server_default=text("'0'"), doc=u""" rtmp是否多码率 默认为0 """, info=CustomField)
    wordsize = Column(SmallInteger, nullable=False, server_default=text("'22'"), doc=u""" 弹幕字体大小，默认为22 """, info=CustomField)
    wordcolor = Column(String(32), nullable=False, server_default=text("'0x000000'"), doc=u"""弹幕字体颜色""", info=CustomField)
    movespeed = Column(SmallInteger, nullable=False, server_default=text("'8'"), doc=u""" 弹幕浮层速度 """, info=CustomField)
    wordfont = Column(String(32), nullable=False, server_default=text(u"'微软雅黑'"), doc=u"""弹幕字体""", info=CustomField)
    wordalpha = Column(Numeric(10, 2), nullable=False, server_default=text("'0.55'"), doc=u""" 弹幕文字透明度 """, info=CustomField)

    mps_config = Column(Text, nullable=True, doc=u"""mps_config配置 json 字符串""", info=CustomField)

    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

class PlayerAodian(Base):
    u"""Aodian 播放器"""
    __tablename__ = 'player_aodian'
    player_id = Column(Integer, primary_key=True, doc=u"""对应 PlayerBase player_id""", info=SortableField | InitializeField)

    # rtmpUrl: ''  // rtmp地址
    # hlsUrl: ''  // hls地址

    width = Column(String(32), nullable=False, server_default=text("'100%'"), doc=u"""播放器宽度，可用数字、百分比等""", info=CustomField)
    height = Column(String(32), nullable=False, server_default=text("'100%'"), doc=u"""播放器高度，可用数字、百分比等""", info=CustomField)
    autostart = Column(SmallInteger, nullable=False, server_default=text("'0'"), doc=u""" 是否自动播放，默认为0 """, info=CustomField)
    controlbardisplay = Column(String(32), nullable=False, server_default=text("'disable'"), doc=u"""是否显示控制栏，值为：disable、enable  默认为disable""", info=CustomField)
    isclickplay = Column(SmallInteger, nullable=False, server_default=text("'0'"), doc=u""" 是否单击播放，默认为0 """, info=CustomField)
    isfullscreen = Column(SmallInteger, nullable=False, server_default=text("'1'"), doc=u""" 是否双击全屏，默认为1 """, info=CustomField)
    stretching = Column(SmallInteger, nullable=False, server_default=text("'1'"), doc=u""" 设置全屏模式,1代表按比例撑满至全屏,2代表铺满全屏,3代表视频原始大小,默认值为1 hls初始设置不支持，手机端不支持 默认为 1 """, info=CustomField)
    defvolume = Column(SmallInteger, nullable=False, server_default=text("'80'"), doc=u""" 默认音量，默认为80 """, info=CustomField)

    bufferlength = Column(SmallInteger, nullable=False, server_default=text("'3'"), doc=u""" 视频缓冲时间，默认为3秒，hls不支持 """, info=CustomField)
    maxbufferlength = Column(SmallInteger, nullable=False, server_default=text("'3'"), doc=u""" 最大视频缓冲时间，默认为3秒，hls不支持 """, info=CustomField)
    adveDeAddr = Column(String(128), nullable=False, server_default=text("''"), doc=u"""封面图片url""", info=CustomField)
    adveWidth = Column(String(32), nullable=False, server_default=text("'100%'"), doc=u"""封面图宽度""", info=CustomField)
    adveHeight = Column(String(32), nullable=False, server_default=text("'100%'"), doc=u"""封面图高度""", info=CustomField)
    adveReAddr = Column(String(128), nullable=False, server_default=text("''"), doc=u"""封面图点击链接""", info=CustomField)

    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

class PlayerAli(Base):
    u"""Ali 播放器"""
    __tablename__ = 'player_ali'
    player_id = Column(Integer, primary_key=True, doc=u"""对应 PlayerBase player_id""", info=SortableField | InitializeField)

    # source:""  // 视频源地址  支持 trmp hls flv

    width = Column(String(32), nullable=False, server_default=text("'100%'"), doc=u"""播放器宽度，可用100px、百分比等""", info=CustomField)
    height = Column(String(32), nullable=False, server_default=text("'100%'"), doc=u"""播放器高度，可用100px、百分比等""", info=CustomField)

    autoplay = Column(SmallInteger, nullable=False, server_default=text("'1'"), doc=u"""自动播放 默认1""", info=CustomField)
    isLive = Column(SmallInteger, nullable=False, server_default=text("'1'"), doc=u"""是否直播 默认1""", info=CustomField)
    playsinline = Column(SmallInteger, nullable=False, server_default=text("'1'"), doc=u"""行内播放 默认 1""", info=CustomField)
    controlBarVisibility = Column(String(32), nullable=False, server_default=text("'always'"), doc=u"""控制栏显示 默认always """, info=CustomField)
    useH5Prism = Column(SmallInteger, nullable=False, server_default=text("'0'"), doc=u"""强制使用h5 默认 0""", info=CustomField)
    useFlashPrism = Column(SmallInteger, nullable=False, server_default=text("'0'"), doc=u"""强制使用flash 默认 0""", info=CustomField)
    cover = Column(String(128), nullable=False, server_default=text("'100%'"), doc=u"""封面图片url""", info=CustomField)
    skinLayout = Column(Text, nullable=True, doc=u"""layout配置 json 字符串""", info=CustomField)

    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

_playerMap = {
    'player_mps': PlayerMps,
    'player_aodian': PlayerAodian,
    'player_ali': PlayerAli,
}

class PlayerUnion(g.Union):
    u"""播放器配置"""

    _type_key = ('player_type', {k: BuildType(v) for k, v in _playerMap.items()})

    class Meta:
        types = map(lambda v: BuildType(v), _playerMap.values())


from admin_base import AdminUser