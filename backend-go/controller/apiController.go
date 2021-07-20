package controller

import (
	"fastduck/apidoc/request"
	"fastduck/apidoc/service"
	"fmt"
	"net/http"

	"github.com/gin-gonic/gin"
)

func ApiList(c *gin.Context) {

	var req request.ApiListRequest
	err := c.ShouldBindQuery(&req)
	if err != nil {
		fmt.Println(err.Error())
	}

	total, list := service.ApiList(req.ProjectId, req.Page, req.PageSize, req.SortField, req.IsDesc)

	c.JSON(http.StatusOK, gin.H{
		"total": total,
		"list":  list,
	})

}
