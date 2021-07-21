package controller

import (
	"fastduck/apidoc/global"
	"fastduck/apidoc/request"
	"fastduck/apidoc/response"
	"fastduck/apidoc/service"

	"github.com/gin-gonic/gin"
	"go.uber.org/zap"
)

//ApiList api列表
func ApiList(c *gin.Context) {

	url := "ttttt"
	global.MyLogger.Sugar().Errorf("test", zap.Any("url", url))

	var req request.ApiListRequest
	err := c.ShouldBindQuery(&req)
	if err != nil {
		response.FailWithMessage(err.Error(), c)
	}

	total, list := service.ApiList(req.ProjectId, req.Page, req.PageSize, req.SortField, req.IsDesc)
	response.OkWithData(response.ListResponse{Total: total, List: list}, c)
}

//ApiDetailById api详情
func ApiDetailById(c *gin.Context) {
	var req request.IdRequest
	err := c.ShouldBindQuery(&req)
	if err != nil {
		response.FailWithMessage(err.Error(), c)
	}

	a := service.ApiDetailById(req.Id)
	response.OkWithData(a, c)
}

//ApiDelete 删除api
func ApiDelete(c *gin.Context) {
	var req request.IdRequest
	err := c.ShouldBindJSON(&req)
	if err != nil {
		response.FailWithMessage(err.Error(), c)
	}

	a := service.ApiDetailById(req.Id)
	response.OkWithData(a, c)
}

//ApiCreate 创建api
func ApiCreate(c *gin.Context) {

}

//ApiUpdate 更新api
func ApiUpdate(c *gin.Context) {

}
