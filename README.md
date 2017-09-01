# 数据模型

数据模型使用`GraphQL`定义，后端使用`graphql-php`实现


> [GraphQL](https://github.com/facebook/graphql) GraphQL is a query language and execution engine tied to any backend service. [doc](http://facebook.github.io/graphql/)

> [graphql-php](https://github.com/webonyx/graphql-php) A PHP port of GraphQL reference implementation [doc](http://webonyx.github.io/graphql-php/)

安装php依赖

``` shell
composer update
```

# DEMO 

POST到接口 `/api/GraphQLApi/exec` 参数 `query` 查询语句，`variables` 查询绑定变量

## 类型实现

继承并实现 GraphQL 中的数据模型  实现类型注册

## 接口测试 [GraphiQL]()

[接口测试](/static/GraphiQL/)


``` gql
{
  hello,
  user(user_id:1001){
    user_id,
    nick,
    avatar
  }
}
```

# 测试数据

测试数据 位于 `db-migrate` 目录下

使用 `Flask-SQLAlchemy` 和 `SQLAlchemy-migrate` 管理数据库部署

> 文档 [Flask 中的数据库](http://www.pythondoc.com/flask-mega-tutorial/database.html#id4)

## 创建数据库
``` shell
python db_create.py
```

## 增加数据库版本
``` shell
python db_migrate.py
```

## 数据库升级
``` shell
python db_upgrade.py
```

## 测试数据导入
``` shell
python db_seed.py
```

# 前端页面

安装js依赖

``` shell
npm install cnpm -g
cnpm install webpack -g
cnpm install
```

打包文件
``` shell
webpack -w
```


