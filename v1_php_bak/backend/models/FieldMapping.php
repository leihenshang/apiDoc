<?php

namespace app\models;

use Throwable;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

/**
 * This is the model class for table "{{%field_mapping}}".
 *
 * @property string $id
 * @property string $field 字段
 * @property string $type 类型
 * @property string $description 描述
 * @property string $is_deleted 0正常1删除
 * @property string $create_time 创建时间
 * @property int $project_id 项目id
 */
class FieldMapping extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%field_mapping}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['field', 'type', 'project_id'], 'required', 'on' => self::SCENARIO_CREATE],
            [['create_time'], 'safe'],
            [['project_id', 'id'], 'integer', 'min' => 1],
            ['id', 'required', 'on' => [self::SCENARIO_UPDATE, self::SCENARIO_DELETE]],
            [['field', 'type', 'description', 'is_deleted'], 'string', 'max' => 100],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['field', 'type', 'description', 'project_id'];
        $scenarios[self::SCENARIO_UPDATE] = ['field', 'type', 'description', 'id','project_id'];
        $scenarios[self::SCENARIO_DELETE] = ['id'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'field' => '字段',
            'type' => '类型',
            'description' => '描述',
            'is_deleted' => '0正常1删除',
            'create_time' => '创建时间',
        ];
    }

    /**
     * 创建和保存数据
     * @return array|string
     */
    public function createData()
    {
        //判断field不能够重复
        $field = $this->findOne(['field' => $this->field,'project_id' => $this->project_id]);
        if ($field) {
            return '字段重复';
        }

        if (!$this->save()) {
            return '保存数据失败:' . current($this->getFirstErrors());
        }

        return $this->toArray();
    }

    /**
     * 列表
     * @param int $projectId 项目id
     * @return array|ActiveRecord[]
     * @throws
     */
    public function dataList(int $projectId)
    {
        return self::find()->where(['is_deleted' => self::IS_DELETED['no'],'project_id' => $projectId])->orderBy('create_time desc')->all();
    }

    /**
     * 更新
     * @return bool|false|int|string
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function updateData()
    {
        $res = self::findOne($this->id);
        if (!$res) {
            return '没有找到可更新数据';
        }

        //判断field不能够重复
        $field = $this->findOne(['field' => $this->field,'project_id' => $this->project_id]);
        if ($field) {
            return '字段重复';
        }

        $res->attributes = $this->toArray();
        return $res->update(false, ['field', 'type', 'description']);
    }

    /**
     * 删除
     * @return bool|false|int|string
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function deleteData()
    {
        $res = self::findOne($this->id);
        if (!$res) {
            return '没有找到可删除数据';
        }

        return $res->delete();
    }
}
