# coding: utf-8
from base_type import *
from app import db, app
Base = db.Model


##############################################################
####################		SiteMgrUser		##################
##############################################################

class SiteMgrUser(Base):
    u"""网站管理员 用户表"""
    __tablename__ = 'site_mgr_user'

    mgr_id = Column(Integer, primary_key=True, doc=u"""管理员id自增""", info=SortableField)
    mgr_slug = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""管理员 slug，可修改""", info=CustomField | SortableField)

    name = Column(String(32), nullable=False, index=True, unique=True, server_default=text("''"), doc=u"""管理员登陆用户名，可修改""", info=CustomField | SortableField)

    pasw = Column(String(32), nullable=False, server_default=text("''"), doc=u"""管理员登录密码，存储加盐md5""", info=HiddenField)
    pasw_time = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""上一次修改密码的时间""", info=SortableField)
    login_ip = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""上次登录ip""", info= SortableField)
    login_location = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""field op_location""", info= SortableField)

    login_count = Column(Integer, nullable=False, server_default=text("'0'"), doc=u"""登录次数""", info= SortableField)
    login_time = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""上次登陆时间""", info= SortableField)

    title = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""管理员标题，用于显示""", info=CustomField | SortableField)
    avator = Column(String(128), nullable=False, server_default=text("''"), doc=u"""管理员头像，用于显示""", info=CustomField)
    email = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""邮箱地址 未使用""", info=CustomField | SortableField)
    cellphone = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""手机号码 未使用""", info=CustomField | SortableField)

    state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""状态 参见 StateEnum 枚举""", info=BitMask(CustomField | SortableField, StateEnum))
    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

class SiteMgrUserPagination(g.ObjectType):
    u''' SiteMgrUser 分页查询 列表'''
    rows = List(SiteMgrUser, description=u'当前查询 SiteMgrUser 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')



##############################################################
####################		ArticleClassify		##################
##############################################################

class ArticleClassify(Base):
    u'''首页  文章分类'''
    __tablename__ = 'article_classify'

    classify_id = Column(Integer, primary_key=True, doc=u"""文章分类主键id""", info=SortableField)
    classify_title = Column(String(32), nullable=False, server_default=text("''"), doc=u"""分类 标题""", info=CustomField)
    classify_keywords = Column(String(128), nullable=False, server_default=text("''"), doc=u"""分类 关键字""", info=CustomField)
    classify_description = Column(String(128), nullable=False, server_default=text("''"), doc=u"""分类 描述""", info=CustomField)
    classify_img = Column(String(128), nullable=False, server_default=text("''"), doc=u"""分类图片""", info=CustomField)

    rank = Column(Integer, nullable=False, server_default=text("'0'"), index=True, doc=u"""分类排序依据""", info=CustomField)

    state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""状态 参见 StateEnum 枚举""", info=BitMask(CustomField | SortableField, StateEnum))
    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class ArticleClassify(SQLAlchemyObjectType):
            class Meta:
                model = cls

            classifyArticleList = Field(lambda :ArticleListPagination, description=u'分类下 文章列表',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),

                article_id=g.Argument(g.Int, default_value=0, description=u'检索 文章 id'),
                article_title=g.Argument(g.String, default_value='', description=u'模糊检索 article_title'),
                article_description=g.Argument(g.String, default_value='', description=u'模糊检索 article_description'),
                article_keywords=g.Argument(g.String, default_value='', description=u'模糊检索 article_keywords'),
                article_author=g.Argument(g.String, default_value='', description=u'模糊检索 article_author'),
                article_from=g.Argument(g.String, default_value='', description=u'模糊检索 article_from'),

                article_date=g.Argument(DateRange, default_value=None, description=u'范围检索 article_date'),
                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                state=g.Argument(StateEnum, default_value=0, description=u'检索 状态枚举值'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_classifyArticleList(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                query = ArticleList.query.filter_by(classify_id = self.classify_id) # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(ArticleList, SortableField))
                return ArticleListPagination(rows=rows, pageInfo=pageInfo)

        return ArticleClassify


##############################################################
####################		ArticleList		##################
##############################################################

class ArticleList(Base):
    u'''首页 文章列表'''
    __tablename__ = 'article_list'

    article_id = Column(Integer, primary_key=True, doc=u"""文章主键id""", info=SortableField)
    classify_id = Column(Integer, nullable=False, index=True, doc=u"""文章所属分类id""", info=CustomField)

    article_title = Column(String(64), nullable=False, server_default=text("''"), index=True, doc=u"""文章 标题""", info=CustomField)
    article_keywords = Column(String(128), nullable=False, server_default=text("''"), doc=u"""文章 关键字""", info=CustomField)
    article_description = Column(String(128), nullable=False, server_default=text("''"), doc=u"""文章 描述""", info=CustomField)

    article_date = Column(String(32), nullable=False, server_default=text("''"), index=True, doc=u"""文章发布时间""", info=CustomField)
    article_author = Column(String(64), nullable=False, server_default=text("''"), index=True, doc=u"""文章发布者""", info=CustomField)
    article_from = Column(String(64), nullable=False, server_default=text("''"), index=True, doc=u"""文章来源""", info=CustomField)

    view_count = Column(Integer, nullable=False, server_default=text("'0'"), index=True, doc=u"""文章观看次数""", info=CustomField)
    article_html = Column(Text, nullable=False, doc=u"""文章内容 html""", info=CustomField)
    article_text = Column(Text, nullable=False, doc=u"""文章内容 text""", info=CustomField)

    rank = Column(Integer, nullable=False, server_default=text("'0'"), index=True, doc=u"""文章排序依据""", info=CustomField)

    state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""状态 参见 StateEnum 枚举""", info=BitMask(CustomField | SortableField, StateEnum))
    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

class ArticleListPagination(g.ObjectType):
    u''' ArticleList 分页查询 列表'''
    rows = List(ArticleList, description=u'当前查询 ArticleList 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')


##############################################################
####################		HelpDocList		##################
##############################################################

class HelpDocList(Base):
    u'''首页 帮助文档列表'''
    __tablename__ = 'help_doc_list'
    doc_id = Column(Integer, primary_key=True, doc=u"""文档主键id""", info= SortableField)
    img = Column(String(128), nullable=False, server_default=text("''"), doc=u"""图片""", info=CustomField | SortableField)
    q_desc = Column(String(128), nullable=False, server_default=text("''"), index=True, doc=u"""问题描述""", info=CustomField | SortableField)
    a_html = Column(Text, nullable=False, doc=u"""答案内容 html""", info=CustomField | SortableField)
    rank = Column(Integer, nullable=False, server_default=text("'0'"), index=True, doc=u"""文档排序依据""", info=CustomField | SortableField)

    state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""状态 参见 StateEnum 枚举""", info=BitMask(CustomField | SortableField, StateEnum))
    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)


##############################################################
####################		SendLog		##################
##############################################################

class SendLog(Base):
    u"""客户超出套餐 信息提醒记录"""
    __tablename__ = 'send_log'

    send_id = Column(Integer, primary_key=True, doc=u"""自增主键""", info=SortableField)
    admin_id = Column(Integer, nullable=False, index=True, doc=u"""用户id""", info=CustomField | SortableField)
    admin_slug = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""用户类型 标注 """, info= SortableField | CustomField)
    business_belong = Column(Integer, nullable=False, index=True, doc=u"""所属商务id""", info=CustomField | SortableField)

    sender_args = Column(Text, doc=u"""发送的文本附加信息 json """, info=CustomField)
    sender_addr = Column(String(255), nullable=False, index=True, doc=u"""发送目标地址""", info=CustomField | SortableField)
    sender_type = Column(String(16), nullable=False, index=True, doc=u"""通知类型  email tel""", info=CustomField | SortableField)
    sender_result = Column(String(16), nullable=False, index=True, doc=u"""发送  结果 """, info=CustomField | SortableField)
    sender_error = Column(String(255), nullable=False, index=True, doc=u"""发送  错误 """, info=CustomField | SortableField)

    type = Column(SmallInteger,nullable=False, index=True, doc=u"""通知类型  1 套餐超出 2欠费 3套餐过期""", info=CustomField | SortableField)
    sender_msg = Column(Text, doc=u"""发送的文本内容""", info=CustomField)

    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class SendLog(SQLAlchemyObjectType):
            class Meta:
                model = cls

            admin = Field(lambda :AdminUser, description=u'操作目标 用户 信息')
            def resolve_admin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.admin_id).first() \
                        if self.admin_id \
                            else None

        return SendLog

class SendLogPagination(g.ObjectType):
    u''' SendLog 分页查询 列表'''
    rows = List(SendLog, description=u'当前查询 SendLog 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')


##############################################################
####################		SiteOpRecord		##################
##############################################################


class SiteOpRecord(Base):
    u"""table site_op_record"""
    __tablename__ = 'site_op_record'

    id = Column(BigInteger, primary_key=True, doc=u"""主键""", info=SortableField)
    op_type = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""操作类型  0 未知  1 插入  2 更改 3 删除""", info=CustomField | SortableField)
    op_table = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""修改数据 表名""", info=CustomField | SortableField)
    op_prikey = Column(String(32), nullable=False, index=True, doc=u"""操作的数据表  主键 名称""", info=CustomField | SortableField)

    op_uid = Column(Integer, nullable=False, server_default=text("'0'"), doc=u"""操作者  admin_id or mgr_id  尽可能 尝试记录""", info=CustomField | SortableField)
    op_prival = Column(BigInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""操作的 本条记录的 主键 id""", info=CustomField | SortableField)

    op_args = Column(Text, nullable=False, doc=u"""本次操作的 参数""", info=CustomField)
    op_diff = Column(Text, nullable=False, doc=u"""操作前后 记录数值 差分""", info=CustomField)

    op_ip = Column(String(32), nullable=False, doc=u"""field op_ip""", info=CustomField | SortableField)
    op_location = Column(String(32), nullable=False, index=True, doc=u"""field op_location""", info=CustomField | SortableField)
    op_uri = Column(String(255), nullable=False, server_default=text("''"), doc=u"""操作 来源 url""", info=CustomField | SortableField)
    op_refer = Column(String(255), nullable=False, server_default=text("''"), doc=u"""操作 refer""", info=CustomField | SortableField)

    last_value = Column(Text, nullable=False, doc=u"""上一次 记录的值  使用 json 序列化""", info=CustomField)
    this_value = Column(Text, nullable=False, doc=u"""更改之后本条记录""", info=CustomField)

    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)



from admin_base import AdminUser

