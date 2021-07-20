package controller

import (
	"net/http"

	"github.com/gin-gonic/gin"
)

func ApiTest(c *gin.Context) {
	c.JSON(http.StatusOK, gin.H{
		"msg": "this is api test",
	})

}
