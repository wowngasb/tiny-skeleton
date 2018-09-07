# coding: utf-8
from base_type import *
from app import db, app
Base = db.Model


##############################################################
####################		StreamUnion		##################
##############################################################

'''
{
    "id": 23,
    "live_id": "",
    "dsc": "",
    "account": "aaa",
    "password_str": "123456",
    "state": 1,
    "create_from": "finance",
    "password": "e10adc3949ba59abbe56e057f20f883e",
    "play_rtmp_url": "",
    "play_hls_url": "",
    "PubUrlPub": "push.wenshunsoft.com",
    "PublishRoomInfo": "wenshun",
    "stream": "xxx",
    "PubWay": "rtmp",
    "StrKey": null,
    "ADSampleRate": "44100",
    "ADChannels": "2",
    "ADBitperSample": "16",
    "ADBitRate": "32",
    "MaxOutResolution": "1280*720",
    "VDIntervalSecond": "1",
    "VD_method": "cbr",
    "VDFPS": "7",
    "VDBitRate": "500",
    "VC_method": "cbr",
    "VCFPS": "10",
    "VCBitRate": "500",
    "VF_method": "vbr",
    "VFFPS": "7",
    "VFBitRate": "30"
}
'''

class StreamBase(Base):
    u"""视频流"""
    __tablename__ = 'stream_base'
    stream_id = Column(Integer, primary_key=True, doc=u"""自增主键""", info=SortableField)
    stream_name = Column(String(64), nullable=False, server_default=text("''"), doc=u"""视频流名称 用于标识""", info=CustomField)
    stream_type = Column(String(32), nullable=False, index=True, doc=u"""视频流类型 参见 StreamTypeEnum""", info=BitMask(InitializeField | SortableField, StreamTypeEnum))
    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""对应 parent 类型 后台用户admin_id""", info=InitializeField | SortableField)
    agent_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""对应 parent 类型 后台用户agent_id""", info=InitializeField | SortableField)
    live_state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""直播状态 参见 LiveStateEnum""", info=BitMask(CustomField | SortableField, LiveStateEnum))

    vod_id = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""视频点播 vod_id """, info=CustomField | SortableField)

    mcs_account = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""直播工具 帐号""", info=CustomField | SortableField)
    mcs_password = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""直播工具 密码""", info=CustomField | SortableField)
    mcs_stream = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""直播工具 stream""", info=CustomField | SortableField)
    mcs_app = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""直播工具 app""", info=CustomField | SortableField)
    mcs_vhost = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""直播工具 host""", info=CustomField | SortableField)
    last_mcs_vhost = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""上一个 直播工具 host""", info=CustomField | SortableField)

    state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""状态 参见 StateEnum 枚举""", info=BitMask(CustomField | SortableField, StateEnum))
    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class StreamBase(SQLAlchemyObjectType):
            class Meta:
                model = cls

            stream = Field(lambda :StreamUnion, description=u'视频流 信息')
            def resolve_stream(self, args, context, info):
                return _streamMap[self.stream_type].query.filter_by(stream_id=self.stream_id).first() \
                        if self.stream_id and self.stream_type in _streamMap \
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

        return StreamBase


class StreamBasePagination(g.ObjectType):
    u''' StreamBase 分页查询 列表'''
    rows = List(StreamBase, description=u'当前查询 StreamBase 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')

class StreamMcs(Base):
    u"""直播工具 视频流"""
    __tablename__ = 'stream_mcs'
    stream_id = Column(Integer, primary_key=True, doc=u"""对应 StreamBase stream_id""", info=SortableField | InitializeField)

    mcs_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""对应mcs配置id""", info=CustomField)  # 根据接口获取MCS账号数据
    mcs_note = Column(String(128), nullable=False, server_default=text("''"), doc=u"""对应 mcs 备注信息""", info=CustomField)
    mcs_config = Column(Text, nullable=True, doc=u"""mcs配置 json 字符串""", info=CustomField)

    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

class StreamVod(Base):
    u"""点播视频文件 视频流"""
    __tablename__ = 'stream_vod'
    stream_id = Column(Integer, primary_key=True, doc=u"""对应 StreamBase stream_id""", info=SortableField | InitializeField)

    vod_id = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""点播文件对应唯一id""", info=CustomField)
    vod_snaptshot = Column(String(128), nullable=False, server_default=text("''"), doc=u"""点播文件对应封面图""", info=CustomField)

    m3u8_id = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""点播文件对应唯一id""", info=CustomField)
    mp4_id = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""点播文件对应唯一id""", info=CustomField)
    flv_id = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""点播文件对应唯一id""", info=CustomField)

    m3u8_url = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""m3u8点播地址""", info=CustomField)
    mp4_url = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""mp4_url点播地址""", info=CustomField)
    flv_url = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""flv_url点播地址""", info=CustomField)

    v_streamname = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""录制 StreamName""", info=CustomField | SortableField)
    v_ossbucket = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""录制 OssBucket""", info=CustomField | SortableField)
    v_domainname = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""录制 DomainName""", info=CustomField | SortableField)
    v_appname = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""录制 AppName""", info=CustomField | SortableField)
    v_ossendpoint = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""录制 OssEndpoint""", info=CustomField | SortableField)

    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""录制来源 admin_id""", info=InitializeField | SortableField)
    v_stream_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""录制来源 stream_id""", info=InitializeField | SortableField)
    v_room_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""录制来源 room_id""", info=InitializeField | SortableField)

    v_height = Column(Integer, nullable=False, server_default=text("'0'"), doc=u"""录制 高度""", info=InitializeField | SortableField)
    v_width = Column(Integer, nullable=False, server_default=text("'0'"), doc=u"""录制 高度""", info=InitializeField | SortableField)
    v_duration = Column(Float, nullable=False, index=True, server_default=text("'0'"), doc=u"""录制 长度""", info=InitializeField | SortableField)

    v_createtime = Column(DateTime, nullable=False, index=True, server_default=text("'0000-00-00 00:00:00'"), doc=u"""录制 CreateTime""", info=SortableField)
    v_starttime = Column(DateTime, nullable=False, index=True, server_default=text("'0000-00-00 00:00:00'"), doc=u"""录制 StartTime""", info=SortableField)
    v_endtime = Column(DateTime, nullable=False, index=True, server_default=text("'0000-00-00 00:00:00'"), doc=u"""录制 EndTime""", info=SortableField)

    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

class StreamVodPagination(g.ObjectType):
    u''' StreamVod 分页查询 列表'''
    rows = List(StreamVod, description=u'当前查询 StreamVod 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')

class StreamPull(Base):
    u"""拉流直播 视频流"""
    __tablename__ = 'stream_pull'
    stream_id = Column(Integer, primary_key=True, doc=u"""对应 StreamBase stream_id""", info=SortableField | InitializeField)

    pull_id = Column(String(128), nullable=False, server_default=text("''"), doc=u"""拉流对应唯一id""", info=CustomField)
    rtmp_url = Column(String(128), nullable=False, server_default=text("''"), doc=u"""拉流输出rtmp 地址""", info=CustomField)

    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)



_streamMap = {
    'stream_mcs': StreamMcs,
    'stream_vod': StreamVod,
    'stream_pull': StreamPull,
}

class StreamUnion(g.Union):
    u"""播放器配置"""

    _type_key = ('stream_type', {k: BuildType(v) for k, v in _streamMap.items()})

    class Meta:
        types = map(lambda v: BuildType(v), _streamMap.values())

from admin_base import AdminUser