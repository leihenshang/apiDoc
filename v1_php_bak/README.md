# API-DOC

## 概览
api-doc 是一个简单高效的api接口文档和项目文档记录应用。支持markdown记录项目文档,极大的方便了前后台对于接口文档和项目文档的查看、使用。后端采用PHP编写，前端使用VUE编写。

## 鸣谢列表


 <img src="./jetbrains.png" width = "200" height = "218.6" alt="图片名称" align=center />


- 感谢 `JetBrains` 提供的免费IDE支持！
- 相关: [JetBrains网站](https://www.jetbrains.com/?from=apiDoc)

### 架构
该项目为前后台分离项目。
- php7.2(Yii2框架)提供后端能力
- vue2.0作为前端开发框架
- mysql5.7 作为数据库
  
### 文件夹
- front 前端文件夹
- backend 后端文件夹

这两个文件夹下还各包含一个README.MD，为各自的其他说明。

### 安装方法
1. 进入front（前端)文件夹,首先执行yarn install安装依赖,接着执行yarn serve
2. 进入backend（后端)文件夹,执行composer install安装依赖,接着执行php yii serve --port=1000

### 数据库初始化
- 在mysql中执行 database/mysql.sql 

## 仓库地址
- github:https://github.com/leihenshang/apiDoc
- gitee:https://gitee.com/leihenshang/apiDoc

## 初始化超级管理员

在backend目录下执行  php yii init/user
超级管理员忘记密码 php yii init/user true


## 项目图片
![登录](https://images.gitee.com/uploads/images/2020/0531/222925_7c0239aa_1719135.png "start.png")
![api概述](https://images.gitee.com/uploads/images/2020/0531/222953_cf831496_1719135.png "detail.png")
![api详情](https://images.gitee.com/uploads/images/2020/0531/223008_68e4cfa8_1719135.png "api-detail.png")
![项目文档](https://images.gitee.com/uploads/images/2020/0531/223021_69ae4f2e_1719135.png "doc.png")
![api列表](https://images.gitee.com/uploads/images/2020/0531/223047_4d5916ce_1719135.png "api-list.png")

