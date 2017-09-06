# coding: utf-8
import os
from app import BuildPHP, BuildGO, BuildJAVA, schema, tables

def main():
    _output = lambda tag: os.path.join(os.getcwd(), 'output', tag)
    _output = lambda s: os.path.join( os.path.dirname(os.path.dirname(os.getcwd())), 'app', 'api')

    BuildPHP(schema=schema, tables=tables, output=_output('phpsrc')).build()
    BuildGO(schema=schema, tables=tables, output=_output('phpsrc')).build()
    BuildJAVA(schema=schema, tables=tables, output=_output('phpsrc')).build()


if __name__ == '__main__':
    main()

