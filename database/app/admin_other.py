# coding: utf-8
from base_type import *
from app import db, app
Base = db.Model


##############################################################
################		AdminCountly		##################
##############################################################

class AdminCountly(Base):
    u"""用户统计服务   总公司 Countly App 记录，每个总公司对应一个Countly App  用于统计详细信息（暂时未使用）  每当用户访问频道都会获取 Countly 配置  使用会话心跳机制 统计用户数据"""
    __tablename__ = 'admin_countly'

    admin_id = Column(Integer, primary_key=True, doc=u"""对应 parent 类型 后台用户admin_id""", info=SortableField | InitializeField)
    countly_id = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""countly 统计 App id""", info=InitializeField | SortableField)
    countly_key = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""countly 统计 key""", info=InitializeField | SortableField)
    countly_name = Column(String(64), nullable=False, server_default=text("''"), doc=u"""统计app 名称""", info=InitializeField | SortableField)
    countly_solt = Column(String(64), nullable=False, server_default=text("''"), doc=u"""countly web SDK solt 用于验证 可选""", info=InitializeField | SortableField)

    state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""状态 参见 StateEnum 枚举""", info=BitMask(CustomField | SortableField, StateEnum))
    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class AdminCountly(SQLAlchemyObjectType):
            class Meta:
                model = cls

            admin = Field(lambda :AdminUser, description=u'parent 类型 后台用户 信息')
            def resolve_admin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.admin_id).first() \
                        if self.admin_id \
                            else None

        return AdminCountly


##############################################################
################		AdminAccessControl		##################
##############################################################


class AdminAccessControl(Base):
    u'''客户 旗下子账号 权限设置 访问控制表 每条记录为一项权限'''
    __tablename__ = 'admin_access_control'
    __table_args__ = (
        Index('admin_access_udx', 'admin_id', 'access_type', 'access_value', unique=True),
    )

    id = Column(Integer, primary_key=True, doc=u"""自增id""", info=SortableField)
    admin_id = Column(Integer, nullable=False, doc=u"""管理员 admin_id""", info=InitializeField | SortableField)
    access_type = Column(String(32), nullable=False, index=True, doc=u"""权限类型 可选 room频道， menu菜单，pagetab切换栏""", info=CustomField | SortableField)
    access_value = Column(String(255), nullable=False, doc=u"""权限数值 同具体access_type 相对应""", info=CustomField | SortableField)

    state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""状态 参见 StateEnum 枚举""", info=BitMask(CustomField | SortableField, StateEnum))
    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)


class AdminRecord(Base):
    u"""table admin_record"""
    __tablename__ = 'admin_record'

    id = Column(Integer, primary_key=True, doc=u"""自增主键""", info=SortableField)
    room_id = Column(Integer, nullable=False, server_default=text("'0'"), index=True, doc=u"""操作相关  room_id  尽可能 尝试记录""", info=CustomField | SortableField)
    stream_id = Column(Integer, nullable=False, server_default=text("'0'"), index=True, doc=u"""操作相关  stream_id  尽可能 尝试记录""", info=CustomField | SortableField)
    player_id = Column(Integer, nullable=False, server_default=text("'0'"), index=True, doc=u"""操作相关  player_id  尽可能 尝试记录""", info=CustomField | SortableField)
    admin_id = Column(Integer, nullable=False, server_default=text("'0'"), index=True, doc=u"""操作相关  admin_id  尽可能 尝试记录""", info=CustomField | SortableField)
    mgr_id = Column(Integer, nullable=False, server_default=text("'0'"), index=True, doc=u"""操作相关  mgr_id  尽可能 尝试记录""", info=CustomField | SortableField)

    op_type = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""操作类型  0 未知  1 登录  2 登出 3 其他操作""", info=CustomField | SortableField)
    op_desc = Column(String(128), nullable=False, doc=u"""field op_desc""", info=CustomField)
    op_ref = Column(String(255), nullable=False, doc=u"""field op_ref""", info=CustomField)
    op_url = Column(String(255), nullable=False, doc=u"""field op_url""", info=CustomField)
    op_args = Column(Text, nullable=True, doc=u"""本次操作的 参数""", info=CustomField)
    op_method = Column(String(128), nullable=False, index=True, doc=u"""field op_method""", info=CustomField)

    op_ip = Column(String(20), nullable=False, doc=u"""field op_ip""", info= SortableField)
    op_location = Column(String(32), nullable=False, index=True, doc=u"""field op_location""", info= SortableField)
    op_admin_id = Column(Integer, nullable=False, server_default=text("'0'"), index=True, doc=u"""操作者  admin_id  尽可能 尝试记录""", info=CustomField | SortableField)

    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class AdminRecord(SQLAlchemyObjectType):
            class Meta:
                model = cls

            targetAdmin = Field(lambda :AdminUser, description=u'操作目标 用户 信息')
            def resolve_targetAdmin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.admin_id).first() \
                        if self.admin_id \
                            else None

            opAdmin = Field(lambda :AdminUser, description=u'操作者 用户 信息')
            def resolve_opAdmin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.op_admin_id).first() \
                        if self.op_admin_id \
                            else None

            targetRoom = Field(lambda :LiveRoom, description=u'操作目标 频道 信息')
            def resolve_targetRoom(self, args, context, info):
                return LiveRoom.query.filter_by(room_id=self.room_id).first() \
                        if self.room_id \
                            else None

            targetStream = Field(lambda :StreamBase, description=u'操作目标 频道 信息')
            def resolve_targetStream(self, args, context, info):
                return StreamBase.query.filter_by(stream_id=self.stream_id).first() \
                        if self.stream_id \
                            else None

            targetPlayer = Field(lambda :PlayerBase, description=u'操作目标 频道 信息')
            def resolve_targetPlayer(self, args, context, info):
                return PlayerBase.query.filter_by(player_id=self.player_id).first() \
                        if self.player_id \
                            else None
        return AdminRecord

class AdminRecordPagination(g.ObjectType):
    u''' AdminRecord 分页查询 列表'''
    rows = List(AdminRecord, description=u'当前查询 AdminRecord 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')


from admin_base import AdminUser
from room_base import LiveRoom
from stream_base import StreamBase
from player_base import PlayerBase
