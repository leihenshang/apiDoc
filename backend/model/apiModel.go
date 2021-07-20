package model

type Api struct {
	BasicModel
	APIName           string `json:"api_name"`
	Description       string `json:"description"`
	DevelopLanguage   string `json:"develop_language"`
	FunctionName      string `json:"function_name"`
	GroupID           int    `json:"group_id"`
	GroupIDSecond     int    `json:"group_id_second"`
	HTTPMethodType    string `json:"http_method_type"`
	HTTPRequestHeader string `json:"http_request_header"`
	HTTPRequestParams string `json:"http_request_params"`
	HTTPReturnParams  string `json:"http_return_params"`
	HTTPReturnSample  string `json:"http_return_sample"`
	HTTPReturnType    string `json:"http_return_type"`
	ObjectName        string `json:"object_name"`
	ProjectID         int    `json:"project_id"`
	ProtocolType      string `json:"protocol_type"`
	URL               string `json:"url"`
}

func (Api) TableName() string {
	return "api"
}
