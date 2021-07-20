<?php


namespace app\models;

use Throwable;
use yii\db\StaleObjectException;

/**
 * 项目模型
 * Class Project
 * @package app\models
 * @property string $title 名字
 * @property integer $id id
 * @property integer $p_id id
 * @property integer $project_id id
 * @property integer is_deleted id
 * @property string create_time id
 * @property integer type 1common,2project,3doc,0undefined
 *
 */
class Group extends BaseModel
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_DEL = 'del';
    const SCENARIO_UPDATE = 'update';

    // 1api,2project,3doc,0undefined
    const GROUP_TYPE = [
        'undefined' => [0, '未定义'],
        'api' => [1, 'api'],
        'project' => [2, '项目'],
        'doc' => [2, '文档'],
    ];

    public function rules()
    {
        return [
            ['id', 'required'],
            ['project_id', 'required'],
            [['id', 'p_id', 'project_id', 'is_deleted', 'type'], 'number'],
            ['title', 'required'],
            ['p_id','default','value' => 0 ],
            ['type', 'required', 'on' => self::SCENARIO_CREATE],
            ['title', 'string', 'length' => [1, 100]],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['title', 'project_id', 'type','p_id'];
        $scenarios[self::SCENARIO_DEL] = ['id'];
        $scenarios[self::SCENARIO_UPDATE] = ['title', 'id','p_id'];
        return $scenarios;
    }

    public static function tableName()
    {
        return 'group';
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

        //检查同组名重复
        $res = self::find()
            ->where([
                'project_id' => $this->project_id,
                'type' => $this->type,
                'title' => $this->title
            ]);

            if ($this->p_id) {
                $res->andWhere(['p_id' => $this->p_id]);
            }
        
          $res = $res->one();
      
        if ($res) {
            return '组名不能重复';
        }

        if (!$this->save()) {
            return current($this->getFirstErrors());
        }

        return (int)$this->id;
    }

    /**
     * 编辑数据
     * @param array $request
     * @return bool|mixed
     */
    public function updateData($request)
    {
        $this->attributes = $request;
        if (!$this->validate()) {
            return current($this->getFirstErrors());
        }

        $res = self::findOne($this->id);
        if (!$res) {
            return '没有找到数据';
        }

        $res->attributes = $request;
        if (!$res->save()) {
            return current($this->getFirstErrors());
        }

        return true;
    }

    /**
     * 删除数据
     * @return bool|mixed
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function del()
    {
        if (!$this->validate()) {
            return current($this->getFirstErrors());
        }

        $res = self::findOne($this->id);
        $res->is_deleted =  self::IS_DELETED['yes'];
        if (!$res->save(false)) {
            return current($res->getFirstErrors());
        }

        //清理子分组
        Group::updateAll(['is_deleted' => self::IS_DELETED['yes']],['p_id' => $res->id]);

        if ($res->type === self::GROUP_TYPE['api'][0]) {
            Api::updateAll(['is_deleted' => self::IS_DELETED['yes']], ['group_id' => $res->id]);
        }

        if ($res->type === self::GROUP_TYPE['doc'][0]) {
            Doc::updateAll(['is_deleted' => self::IS_DELETED['yes']], ['group_id' => $res->id]);
        }

        return true;
    }
}
