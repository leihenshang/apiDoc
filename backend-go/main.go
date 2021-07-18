package main

import (
	"fast-duck/goApiDoc/app/api"
	"fmt"
	"net/http"

	"github.com/gin-gonic/gin"
)

func main() {
	fmt.Println("开启第api-doc-go重构第一个版本")
	r := gin.Default()
	r.GET("/ping", func(c *gin.Context) {
		c.JSON(http.StatusOK, gin.H{
			"msg": "pong",
		})
	})

	api.ApiTest()

	r.Run()
}
