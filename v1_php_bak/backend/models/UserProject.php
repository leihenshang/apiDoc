<?php

namespace app\models;

/**
 * This is the model class for table "{{%user_project}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $project_id
 * @property int $is_leader
 * @property int $is_deleted
 * @property string $create_time
 * @property int $permission
 */
class UserProject extends BaseModel
{
    //是否是团队统领人
    const IS_LEADER = ['yes' => 1, 'no' => 0];

    const PERMISSION = [
        'read' => [4, '读'],
        'write' => [2, '写'],
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_project}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'project_id', 'is_leader','permission'], 'integer'],
            [['user_id', 'project_id'], 'required']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户 ID',
            'project_id' => 'Project ID',
            'is_leader' => 'Is Leader',
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['user_id', 'project_id'];
        return $scenarios;
    }
}
