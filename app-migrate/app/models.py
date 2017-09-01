# coding: utf-8
import random, time

from inspect import isclass
from sqlalchemy import Index, types, Column, BigInteger, Integer, SmallInteger, String, Text, DateTime, Float, Numeric, text
from sqlalchemy.inspection import inspect as sqlalchemyinspect

import graphene as g

from grapheneinfo import BuildType, SQLAlchemyObjectType, List, NonNull, Field, _is_graphql, _is_graphql_cls

from grapheneinfo.utils import (
    HiddenField,
    InitializeField,
    EditableField,
    CustomField
)

from app import db, app
Base = db.Model


class TestTable(Base):
    u'''测试数据表'''
    __tablename__ = 'admin_log'

    id = Column(Integer, primary_key=True, doc=u"""自增主键""")
    test_int = Column(Integer, nullable=False, server_default=text("'0'"), doc=u"""test_int""")
    test_string = Column(String(20), nullable=False, doc=u"""test_string""")


class Query(g.ObjectType):
    hello = g.String(name=g.Argument(g.String, default_value="world", description = u'input you name'))
    deprecatedField = Field(g.String, deprecation_reason = 'This field is deprecated!')
    fieldWithException = g.String()

    def resolve_hello(self, args, context, info):
        return 'Hello, %s!' % (args.get('name', ''), )

    def resolve_deprecatedField(self, args, context, info):
        return 'You can request deprecated field, but it is not displayed in auto-generated documentation by default.'

    def resolve_fieldWithException(self, args, context, info):
        raise ValueError('Exception message thrown in field resolver')

tables = [tbl if BuildType(tbl) else tbl for _, tbl in globals().items() if isclass(tbl) and issubclass(tbl, Base) and tbl != Base]
schema = g.Schema(query=Query, types=[BuildType(tbl) for tbl in tables] + [cls for _, cls in globals().items() if _is_graphql_cls(cls)], auto_camelcase = False)
