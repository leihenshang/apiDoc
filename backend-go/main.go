package main

import (
	"fast-duck/goApiDoc/global"
	_ "fast-duck/goApiDoc/global"
	"fast-duck/goApiDoc/route"
	"fmt"
	"strconv"

	"github.com/gin-gonic/gin"
)

func main() {
	fmt.Println("开启api-doc重构第一个版本")
	r := gin.Default()
	//初始化路由
	route.InitRoute(r)
	r.Run(":" + strconv.Itoa(global.MyConf.App.Port))
}
