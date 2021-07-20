<?php

namespace app\models;

/**
 * This is the model class for table "{{%api}}".
 *
 * @property string $id
 * @property string $group_id 组id
 * @property string $project_id 项目id
 * @property string $description 备注
 * @property string $is_deleted 0正常，1删除
 * @property string $create_time
 * @property string $update_time
 * @property int $group_id_second 二级分组
 * @property string $protocol_type 协议
 * @property string $http_method_type Http请求类型
 * @property string $url url
 * @property string $api_name 名称
 * @property string $object_name 对象名
 * @property string $function_name 方法名
 * @property string $develop_language 开发语言
 * @property string $http_request_header http头
 * @property string $http_request_params http请求参数
 * @property string $http_return_type http请求返回值
 * @property string $http_return_sample http响应数据样例
 * @property string $http_return_params http'请求响应参数
 */
class Api extends BaseModel
{

    const SCENARIO_CREATE = 'create';
    const SCENARIO_DEL = 'del';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_LIST = 'list';
    const SCENARIO_DETAIL = 'detail';
    const SCENARIO_UPDATE_RAW = 'update_raw';

    public static $defaultData = [
        'group' => '', //分组
        'protocol' => 'http', //协议
        'description' => '', //说明和备注
        'requestMethod' => 'GET', //http请求方法
        'returnDataType' => 1,  //返回值类型
        'url' => '/test/test/test/st', //http请求URL
        'name' => '', //接口名称
        'objectName' => '', //根对象名
        'functionName' => '', //程序内部方法名
        'developmentLanguage' => '', //接口开发语言
        'requestHeader' => [], //请求头
        'requestParams' => [], //请求参数
        'returnData' => [], //返回参数
        'returnDataSuccess' => '', //返回数据成功
        'returnDataFailed' => '' //返回数据失败
    ];

    /**
     * @var string 数据
     */
    public $data;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%api}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'data'], 'required'],
            [['group_id', 'project_id', 'is_deleted', 'group_id_second'], 'integer'],
            [['data', 'create_time', 'update_time'], 'safe'],
            [['group_id', 'project_id'], 'required', 'on' => self::SCENARIO_CREATE],
            [['description', 'api_name'], 'string', 'max' => 1000],
            [['protocol_type', 'http_method_type', 'object_name', 'function_name', 'develop_language'], 'string', 'max' => 20000],
            [['url', 'http_request_header', 'http_request_params', 'http_return_type', 'http_return_sample', 'http_return_params'], 'string', 'max' => 20000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => '组id',
            'project_id' => '项目id',
            'data' => '数据',
            'description' => '备注',
            'is_deleted' => '0正常，1删除',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'group_id_second' => '二级分组',
            'protocol_type' => '协议',
            'http_method_type' => 'Http请求类型',
            'url' => 'url',
            'api_name' => '名称',
            'object_name' => '对象名',
            'function_name' => '方法名',
            'develop_language' => '开发语言',
            'http_request_header' => 'http头',
            'http_request_params' => 'http请求参数',
            'http_return_type' => 'http请求返回值',
            'http_return_sample' => 'http响应数据样例',
            'http_return_params' => 'http请求响应参数',
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = [
            'data', 'project_id', 'group_id', 'description', 'protocol_type',
            'http_method_type', 'url', 'api_name', 'object_name', 'function_name', 'develop_language',
            'http_request_header', 'http_request_params', 'http_return_type', 'http_return_sample', 'http_return_params'
        ];
        $scenarios[self::SCENARIO_UPDATE] = [
            'data', 'id', 'project_id', 'group_id', 'description', 'protocol_type',
            'http_method_type', 'url', 'api_name', 'object_name', 'function_name', 'develop_language',
            'http_request_header', 'http_request_params', 'http_return_type', 'http_return_sample', 'http_return_params'
        ];
        $scenarios[self::SCENARIO_UPDATE_RAW] = ['id', 'data', 'project_id', 'group_id'];
        $scenarios[self::SCENARIO_DEL] = ['id'];
        $scenarios[self::SCENARIO_LIST] = [];
        $scenarios[self::SCENARIO_DETAIL] = ['id'];
        return $scenarios;
    }

    /**
     * 创建数据
     * @return bool|mixed
     */
    public function createData()
    {
        if (!$this->validate()) {
            return current($this->getFirstErrors());
        }

        //检查groupId和projectId的存在
        if (!Group::findOne($this->group_id)) {
            return '组不存在';
        }

        if (!Project::findOne($this->project_id)) {
            return '项目不存在';
        }

        $tmp = json_decode($this->data, true);
        if (json_last_error()) {
            return '解析数据错误';
        }

        unset($tmp['project_id'], $tmp['group_id']);
        $tmp = array_map(function ($a) {
            if (is_array($a)) {
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            return $a;
        }, $tmp);

        //字段映射处理
        $httpReturnParams = $tmp['http_return_params'] ?? [];
        if ($httpReturnParams) {
            $httpReturnParams = json_decode($httpReturnParams, true);
            if ($httpReturnParams) {
                $field = FieldMapping::find()->where(['project_id' => $this->project_id])->asArray()->all();
                if ($field) {
                    $field = array_combine(array_column($field, 'field'), $field);
                }

                foreach ($httpReturnParams as &$item) {
                    if (isset($field[$item['fieldName']])) {
                        $item['description'] = empty($item['description']) ? $field[$item['fieldName']]['description'] : '';
                        $item['type'] = $field[$item['fieldName']]['type'];
                    }
                }
            }
        }

        if (is_array($httpReturnParams)) {
            $httpReturnParams = json_encode($httpReturnParams, JSON_UNESCAPED_UNICODE);
        }

        $tmp['http_return_params'] = $httpReturnParams;
        $this->attributes = $tmp;
        if (!$this->save()) {
            return current($this->getFirstErrors());
        }

        //记录操作日志
        OperationLog::createLog($this->project_id, UserInfo::$staticUserInfo->id, $this->id, OperationLog::ACTION['create'][0], '接口:' . $this->api_name, OperationLog::OBJECT_TYPE['api'][0]);
        return $this;
    }

    /**
     * 编辑数据
     * @param array $request
     * @return bool|mixed
     */
    public function updateData($request)
    {
        if (!isset($request['data'])) {
            return '缺少data';
        }

        $jsonData = json_decode($request['data'], true);
        if (json_last_error()) {
            return json_last_error_msg();
        }

        unset($jsonData['project_id'], $jsonData['id'], $jsonData['groupId'], $jsonData['id']);
        foreach ($jsonData as $key => $jsonDatum) {
            if ($key === 'http_request_params') {
                $jsonData[$key] = json_encode($jsonDatum, JSON_UNESCAPED_UNICODE);
            }
            if ($key === 'http_return_params') {
                $jsonData[$key] = json_encode($jsonDatum, JSON_UNESCAPED_UNICODE);
            }
            if ($key === 'http_return_sample') {
                $jsonData[$key] = json_encode($jsonDatum, JSON_UNESCAPED_UNICODE);
            }
            if ($key === 'http_request_header') {
                $jsonData[$key] = json_encode($jsonDatum, JSON_UNESCAPED_UNICODE);
            }
        }

        $request = array_merge($request, $jsonData);
        $this->attributes = $request;
        if (!$this->validate()) {
            return current($this->getFirstErrors());
        }

        $this->scenario = self::SCENARIO_UPDATE;
        if (!$this->validate()) {
            return current($this->getFirstErrors());
        }

        $res = self::findOne($this->id);
        if (!$res) {
            return '没有找到数据';
        }

        //检查groupId和projectId的存在
        if (!Group::findOne($this->group_id)) {
            return '组不存在';
        }

        if (!Project::findOne($this->project_id)) {
            return '项目不存在';
        }

        //        $tmp = json_decode($this->data, true);
        //        if (json_last_error()) {
        //            return '解析数据错误';
        //        }

        //        var_dump($tmp);
        ////        var_dump($this->toArray());
        //        die;
        //
        //        $tmp = array_map(function ($a) {
        //            if (is_array($a)) {
        //                return json_encode($a, JSON_UNESCAPED_UNICODE);
        //            }
        //            return $a;
        //        }, $tmp);
        //
        $res->attributes = $request;
        if(!$res->group_id_second) {
            $res->group_id_second = 0;
        }

        if (!$res->save()) {
            return current($this->getFirstErrors());
        }

        //记录操作日志
        OperationLog::createLog($res->project_id, UserInfo::$staticUserInfo->id, $res->id, OperationLog::ACTION['update'][0], '接口:' . $this->api_name, OperationLog::OBJECT_TYPE['api'][0]);

        return true;
    }

    /**
     * 删除数据
     * @return bool|mixed
     */
    public function del()
    {
        if (!$this->validate()) {
            return current($this->getFirstErrors());
        }

        $res = self::findOne($this->id);
        $res->is_deleted = self::IS_DELETED['yes'];
        if (!$res->save(false)) {
            return current($res->getFirstErrors());
        }

        //记录操作日志
        OperationLog::createLog($res->project_id, UserInfo::$staticUserInfo->id, $res->id, OperationLog::ACTION['delete'][0], '接口:' . $res->api_name, OperationLog::OBJECT_TYPE['api'][0]);
        return true;
    }

    /**
     * 数据列表
     * @param int $projectId
     * @param int $ps
     * @param int $cp
     * @param int $groupId
     * @param int $isDeleted
     * @param string $keyword
     * @return Api[]|array
     */
    public function dataList($projectId, $ps, $cp, $groupId = 0, $isDeleted = 0, $keyword = '')
    {
        $this->ps = $ps;
        $this->cp = $cp;

        $res = self::find()
            ->andWhere(['project_id' => $projectId]);

        if ($isDeleted) {
            $res->andWhere(['is_deleted' => self::IS_DELETED['yes']]);
        } else {
            $res->andWhere(['is_deleted' => self::IS_DELETED['no']]);
        }

        if ($groupId > 0) {
            $res->andWhere(['group_id' => $groupId]);
        }

        if ($keyword) {
            $res->andWhere(['or', [
                'LIKE', 'api_name', $keyword . '%', false
            ], [
                'LIKE', 'url', $keyword . '%', false
            ]]);
        }

        $resCount = $res->count();
        $res = $res->limit($this->ps)->offset($this->offset)->all();

        return ['count' => $resCount, 'items' => $res];
    }

    /**
     * api详情
     * @return array
     */
    public function detail()
    {
        $res = self::findOne($this->id)->toArray();
        $res['group_name'] = "";
        if ($res) {
            $res['http_request_params'] = json_decode($res['http_request_params'], JSON_UNESCAPED_UNICODE);
            $res['http_return_params'] = json_decode($res['http_return_params'], JSON_UNESCAPED_UNICODE);
            $res['http_return_sample'] = json_decode($res['http_return_sample'], JSON_UNESCAPED_UNICODE);
            $res['http_request_header'] = json_decode($res['http_request_header'], JSON_UNESCAPED_UNICODE);
        }

        //对调 group_id 为最顶级分组 group_id_second 为子分组

        //获取分组信息
        $groupId = $res['group_id'] ?? 0;
        if ($groupId) {
            $group = Group::find()->where(['id' => $groupId])->one();
            if ($group && $group['p_id']) {
                $res['group_id_second'] = $res['group_id'];
                $res['group_id'] = $group['p_id'];

                $groupParent = Group::find()->where(['id' => $group['p_id']])->one();
                if ($groupParent) {
                    $res['group_name'] .= $groupParent['title'] . ' / ';
                }

            }
            if ($group) {
                $res['group_name'] .= $group['title'];
            }
        }

        if( empty($res['group_name'])) {
            $res['group_name'] = '无';
        }

        return $res;
    }
}
