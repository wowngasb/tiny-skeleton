# coding: utf-8
import sys
import os
from config import SQLALCHEMY_DATABASE_URI
from config import SQLALCHEMY_MIGRATE_REPO
from sqlalchemy.engine import create_engine

from app import models

from sqlalchemy.inspection import inspect as sqlalchemyinspect


from sqlalchemy import schema
from sqlalchemy.ext.compiler import compiles


def addslashes(s, l = ["\\", '"', "'", "\0"]):
    for i in l:
        s = s.replace(i, '\\' + i)
    return s

def update_comment(engine, tablename, tabledoc, col_map):
    dbname = engine.url.database
    tabledoc = tabledoc.strip().encode('utf-8') if tabledoc else ''
    tbl_str = ''' ALTER TABLE `{dbname}`.`{tablename}` COMMENT "{tabledoc}" '''.format(
        dbname = dbname,
        tablename = tablename,
        tabledoc = addslashes(tabledoc),
    )
    tabledoc and engine.execute(tbl_str)
    print 'execute table', tbl_str.decode('utf-8')

    for col, item in col_map.items():
        doc = item.get('doc', '').strip().encode('utf-8') if item.get('doc', '') else ''
        _type = item.get('type', '').strip() if item.get('type', '') else ''
        if not _type:
            continue

        sql_str = ''' ALTER TABLE `{dbname}`.`{tablename}` MODIFY COLUMN `{col}`  {_type} COMMENT "{doc}" '''.format(
            dbname = dbname,
            tablename = tablename,
            col = col,
            doc = addslashes(doc),
            _type = _type,
        )
        doc and engine.execute(sql_str)
        print 'execute col', sql_str.decode('utf-8')

def main():
    engine = create_engine(SQLALCHEMY_DATABASE_URI)
    tables = models.tables
    for _table in tables:
        table = sqlalchemyinspect(_table)
        _type = lambda c: str( schema.CreateColumn(c) ).split(' ', 1)[-1].strip()
        _item = lambda c : {'doc':getattr(c, 'doc', None), 'type': _type(c)}
        col_map = {k: _item(v) for k, v in table.columns.items()}
        tablename = _table.__tablename__
        tabledoc = _table.__doc__
        update_comment(engine, tablename, tabledoc, col_map)

if __name__ == '__main__':
    main()
