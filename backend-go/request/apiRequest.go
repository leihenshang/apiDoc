package request

type ApiListRequest struct {
	ListPageRequest
	ProjectId int `json:"projectId" form:"projectId"`
}
