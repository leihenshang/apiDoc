package controller

import (
	"fastduck/apidoc/request"
	"fastduck/apidoc/response"
	"fastduck/apidoc/service"
	"fmt"

	"github.com/gin-gonic/gin"
)

//ApiList api列表
func ApiList(c *gin.Context) {
	var req request.ApiListRequest
	err := c.ShouldBindQuery(&req)
	if err != nil {
		fmt.Println(err.Error())
	}

	total, list := service.ApiList(req.ProjectId, req.Page, req.PageSize, req.SortField, req.IsDesc)
	response.OkWithData(response.ListResponse{Total: total, List: list}, c)
}

func ApiDetailById(id int64) {
	
}