# coding: utf-8
import os
import sys
from flask import Flask
from flask_sqlalchemy import SQLAlchemy
from flask_graphql import GraphQLView


app = Flask(__name__)
app.debug = True
app.config.setdefault('SQLALCHEMY_TRACK_MODIFICATIONS', True)
db = SQLAlchemy(app)

@app.route('/')
@app.route('/index')
def index():
    with open(os.path.join(os.getcwd(), 'GraphiQL.html'), 'r') as rf:
        return rf.read()

if __name__ == '__main__':
    sys.path.append(os.path.join(os.path.dirname(os.getcwd()), 'app'))
    import models

    _output = lambda tag: os.path.join(os.getcwd(), 'output', tag)
    from codegen import BuildPHP, BuildGO, BuildJAVA
    test = BuildPHP(schema=models.schema, tables=models.tables, output=_output('phpsrc'))
    test.build()
    BuildGO(schema=models.schema, tables=models.tables).build()
    BuildJAVA(schema=models.schema, tables=models.tables).build()

    app.add_url_rule('/graphql', view_func=GraphQLView.as_view('graphql', schema=models.schema, graphiql=True))
    exit(0)

    print 'run'
    app.run(port = 8085,debug = True)

