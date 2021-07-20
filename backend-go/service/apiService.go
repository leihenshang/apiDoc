package service

import "fastduck/apidoc/global"

func ApiList(projectId int, page int, pageSize int, sortField string, isDesc bool) (total int64, list interface{}) {

	query := global.MyDb.Table("api").Where("project_id = ?", projectId)
	query.Count(&total)
	offset := (page - 1) * pageSize
	var results []map[string]interface{}
	list = query.Offset(offset).Limit(pageSize).Find(&results)

	return total, results
}
