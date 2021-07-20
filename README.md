# API-DOC

> php 版本因某些原因暂时封存，在v1_php_bak中查看
## 概览
api-doc 是一个轻(jian)量(lou)的api接口文档和项目文档记录程序。支持markdown文档,方便了前后端开发人员对于接口文档和项目文档的查看、使用。

- github:https://github.com/leihenshang/apiDoc
- gitee:https://gitee.com/leihenshang/apiDoc

## 鸣谢


 <img src="./jetbrains.png" width = "200" height = "218.6" alt="图片名称" align=center />


 感谢[ JetBrains ](https://www.jetbrains.com/?from=apiDoc)提供的IDE支持

## 文件结构
该项目为前后台分离项目
- front 前端
- backend 后端

## 技术栈
  - 后端采用 `golang` 的 `gin` 框架提供接口支持
  - 前端暂时采用 vue2.0
  - 数据库继续使用 mysql5.7
## 运行
1. 进入front（前端)文件夹,首先执行yarn install安装依赖,接着执行yarn serve
2. 进入backend（后端)文件夹,执行 go run main

## 数据库初始化
- 在mysql中执行 sql/mysql.sql 


## 初始化超级管理员

在backend目录下执行  php yii init/user
超级管理员忘记密码 php yii init/user true


## 预览
![登录](https://images.gitee.com/uploads/images/2020/0531/222925_7c0239aa_1719135.png "start.png")
![api概述](https://images.gitee.com/uploads/images/2020/0531/222953_cf831496_1719135.png "detail.png")
![api详情](https://images.gitee.com/uploads/images/2020/0531/223008_68e4cfa8_1719135.png "api-detail.png")
![项目文档](https://images.gitee.com/uploads/images/2020/0531/223021_69ae4f2e_1719135.png "doc.png")
![api列表](https://images.gitee.com/uploads/images/2020/0531/223047_4d5916ce_1719135.png "api-list.png")

