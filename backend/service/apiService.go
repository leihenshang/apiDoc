package service

import (
	"fastduck/apidoc/global"
	"fastduck/apidoc/model"
)

//ApiList api列表
func ApiList(projectId int, page int, pageSize int, sortField string, isDesc bool) (total int64, list []model.Api) {
	query := global.MyDb.Model(&model.Api{}).Where("project_id = ?", projectId)
	query.Count(&total)
	offset := (page - 1) * pageSize
	query.Debug().Offset(offset).Limit(pageSize).Find(&list)
	return total, list
}

//ApiDetailById api详情
func ApiDetailById(id int) (a model.Api) {
	global.MyDb.First(&a, id)
	return
}

//ApiDeleteById 删除api
func ApiDeleteById(id int) (ok bool) {
	var a model.Api
	global.MyDb.First(&a, id)
	if a.ID <= 0 {
		return
	}

	global.MyDb.Delete(&a)
	return true
}
