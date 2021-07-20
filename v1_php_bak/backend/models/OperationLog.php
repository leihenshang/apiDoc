<?php

namespace app\models;

/**
 * This is the model class for table "{{%operation_log}}".
 *
 * @property int $id
 * @property int $is_deleted
 * @property string $create_time
 * @property int $user_id
 * @property int $project_id 项目id
 * @property string $action 动作
 * @property string $content 操作内容
 * @property int $type 1项目2分组3api4用户
 */
class OperationLog extends BaseModel
{
    const ACTION = [
        'create' => ['create', '增加'],
        'delete' => ['delete', '删除'],
        'update' => ['update', '更新'],
        'query' => ['query', '查询'],
        'restore' => ['restore', '恢复'],
    ];

    const OBJECT_TYPE = [
        'project' => [1, '项目'],
        'group' => [2, '分组'],
        'api' => [3, 'api'],
        'user' => [4, '用户'],
        'doc' => [5, '文档'],
    ];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%operation_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_deleted', 'user_id', 'object_id', 'type', 'project_id'], 'integer'],
            [['create_time'], 'safe'],
            [['action', 'project_id'], 'required'],
            [['action'], 'string', 'max' => 100],
            [['content'], 'string', 'max' => 500],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['user_id', 'object_id', 'action', 'content', 'type', 'project_id'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'user_id' => 'User ID',
            'action' => '动作',
            'content' => '操作内容',
            'object_id' => '项目id',
        ];
    }

    /**
     * 保存操作日志
     * @param int $projectId
     * @param int $userId
     * @param int $objectId
     * @param string $action
     * @param string $content
     * @param int $type 类型
     * @return bool|mixed
     */
    public static function createLog(int $projectId, int $userId = 0, int $objectId = 0, string $action = 'unknown', string $content = '默认操作', int $type = 0)
    {
        $arr = [
            'project_id' => $projectId,
            'user_id' => $userId,
            'object_id' => $objectId,
            'action' => $action,
            'content' => (self::ACTION[$action][1] . $content ?: $content),
            'type' => $type
        ];


        $operationLog = new self();
        $operationLog->scenario = self::SCENARIO_CREATE;
        $operationLog->attributes = $arr;
        if (!$operationLog->validate()) {
            return current($operationLog->getFirstErrors());
        }

        if (!$operationLog->save(false)) {
            return current($operationLog->getFirstErrors());
        }

        return true;
    }

}
