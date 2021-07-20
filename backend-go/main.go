package main

import (
	"fast-duck/goApiDoc/global"
	_ "fast-duck/goApiDoc/global"
	"fast-duck/goApiDoc/route"
	"fmt"
	"net/http"
	"strconv"
	"time"

	"github.com/gin-gonic/gin"
)

func main() {
	fmt.Println("开启api-doc重构第一个版本")
	r := gin.New()
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
