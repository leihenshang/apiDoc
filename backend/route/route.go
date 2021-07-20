package route

import (
	"fastduck/apidoc/controller"
	"net/http"

	"github.com/gin-gonic/gin"
)

func InitRoute(r *gin.Engine) {
	//测试连通性
	base := r.Group("/")
	{
		base.GET("/ping", func(c *gin.Context) {
			c.JSON(http.StatusOK, gin.H{
				"msg": "pong",
			})
		})
	}

	//api
	api := r.Group("api")
	{
		api.GET("/list", controller.ApiList)
		api.GET("/detail", controller.ApiDetailById)
		api.GET("/create", controller.ApiCreate)
		api.GET("/delete", controller.ApiDelete)
		api.GET("/update", controller.ApiUpdate)
	}
}
