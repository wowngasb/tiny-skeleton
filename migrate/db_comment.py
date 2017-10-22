# coding: utf-8
import os
from config import SQLALCHEMY_DATABASE_URI
from sqlalchemy.engine import create_engine
from app import models
from sqlalchemy.inspection import inspect as sqlalchemyinspect
from sqlalchemy import schema
import json

from pykl.tiny.dumptable import get_col_doc

def addslashes(s, l = ["\\", '"', "'", "\0"]):
    for i in l:
        s = s.replace(i, '\\' + i)
    return s

def fstr(QUOTES, ESCAPE = '\\'): ##i[si] is " or ', return index of next i[si] without \ before it
    QUOTES = QUOTES.strip()
    def _fstr(index, s, sl):
        _index = index
        while _index<sl and s[_index] != QUOTES:
            _index += 1

        index = _index
        _index += 1
        while _index<sl and s[_index] != QUOTES:
            _index += 2 if s[_index]==ESCAPE else 1

        return _index+1, s[index+1:_index]

    return _fstr

def fixunicode(ustr):
    _, default_1 = fstr('"')(0, ustr, len(ustr))
    _, default_2 = fstr("'")(0, ustr, len(ustr))
    default = default_1 if default_1 else default_2
    if r'\u' in default:
        _default = json.loads('"{0}"'.format(default)).encode('gbk').decode('utf-8')
        ustr = ustr.replace(default, _default).encode('utf-8')
        pass
    return ustr

def update_comment(doc_map, engine, tablename, tabledoc, col_map):
    dbname = engine.url.database
    tabledoc = tabledoc.strip().encode('utf-8') if tabledoc else ''
    tbl_str = ''' ALTER TABLE `{dbname}`.`{tablename}` COMMENT "{tabledoc}" '''.format(
        dbname = dbname,
        tablename = tablename,
        tabledoc = addslashes(tabledoc),
    )
    if doc_map[tablename]['Comment'].encode('utf-8') != tabledoc and tabledoc:
        engine.execute(tbl_str)
        print 'execute table', tbl_str.decode('utf-8')
    else:
        print 'table Comment pass', tablename

    for col, item in col_map.items():
        doc = item.get('doc', '').strip().encode('utf-8') if item.get('doc', '') else ''
        _type = item.get('type', '').strip().encode('utf-8') if item.get('type', '') else ''
        _type = fixunicode(_type)
        if not _type:
            continue
        sql_str = ''' ALTER TABLE `{dbname}`.`{tablename}` MODIFY COLUMN `{col}`  {_type} COMMENT "{doc}" '''.format(
            dbname = dbname,
            tablename = tablename,
            col = col,
            doc = addslashes(doc),
            _type = _type,
        )
        if doc_map[tablename]['Columns'][col]['Comment'].encode('utf-8') != doc and doc:
            engine.execute(sql_str)
            print 'execute col', sql_str.decode('utf-8')
        else:
            print 'table Columns Comment pass', tablename, col

def main():
    _type = lambda c: str( schema.CreateColumn(c) ).split(' ', 1)[-1].strip()
    _item = lambda c : {'doc':getattr(c, 'doc', None), 'type': _type(c)}

    engine, tables = create_engine(SQLALCHEMY_DATABASE_URI), models.tables
    doc_map = get_col_doc(engine)
    for _table in tables:
        table = sqlalchemyinspect(_table)
        update_comment(doc_map, engine, _table.__tablename__, _table.__doc__, {k: _item(v) for k, v in table.columns.items()})

if __name__ == '__main__':
    main()