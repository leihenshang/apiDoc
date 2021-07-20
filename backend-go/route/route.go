package route

import (
	"fastduck/apidoc/controller"
	"net/http"

	"github.com/gin-gonic/gin"
)

func InitRoute(r *gin.Engine) {
	base := r.Group("/")
	{
		base.GET("/ping", func(c *gin.Context) {
			c.JSON(http.StatusOK, gin.H{
				"msg": "pong",
			})
		})

		base.GET("/test", controller.ApiTest)
	}

	api := r.Group("api")
	{
		api.GET("/list", controller.ApiList)
	}
}
