package main

import (
	"fastduck/apidoc/global"
	_ "fastduck/apidoc/global"
	"fastduck/apidoc/route"
	"fmt"
	"net/http"
	"strconv"
	"time"

	"github.com/gin-gonic/gin"
)

func main() {
	fmt.Println("开启api-doc重构第一个版本")

	//设置运行模式
	if global.MyConf.App.IsRelease() {
		fmt.Println("设置模式为", gin.ReleaseMode)
		gin.SetMode(gin.ReleaseMode)
	}

	r := gin.New()

	//记录全部的访问日志
	// r.Use(ginzap.Ginzap(global.MyLogger, time.RFC3339, true))

	//把gin致命错误写入日志
	// r.Use(ginzap.RecoveryWithZap(global.MyLogger, true))

	//初始化路由
	route.InitRoute(r)
	s := &http.Server{
		Addr:           ":" + strconv.Itoa(global.MyConf.App.Port),
		Handler:        r,
		ReadTimeout:    10 * time.Second,
		WriteTimeout:   10 * time.Second,
		MaxHeaderBytes: 1 << 20,
	}
	s.ListenAndServe()

}
