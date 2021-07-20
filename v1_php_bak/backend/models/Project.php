<?php


namespace app\models;

use Throwable;
use yii\db\StaleObjectException;

/**
 * 项目模型
 * Class Project
 * @package app\models
 * @property string $title 标题
 * @property integer $id id
 * @property int $is_deleted
 */
class Project extends BaseModel
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_DEL = 'del';
    const SCENARIO_UPDATE = 'update';

    const TYPE = [
        'web' => ['tag' => 1, 'desc' => 'web'],
        'pc' => ['tag' => 2, 'desc' => 'pc'],
    ];

    public function rules()
    {
        return [
            [['ps', 'cp'], 'integer'],
            ['id', 'required'],
            ['id', 'number'],
            [['title', 'version', 'type'], 'required'],
            ['title', 'string', 'length' => [1, 100]],
            ['description', 'string', 'length' => [1, 100]],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['title', 'description', 'version', 'type'];
        $scenarios[self::SCENARIO_DEL] = ['id'];
        $scenarios[self::SCENARIO_UPDATE] = ['title', 'description', 'version', 'type'];
        return $scenarios;
    }

    public static function tableName()
    {
        return 'project';
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

        //检查项目名是否重复
        $project = self::find()->where([
            'title' => $this->title,
            'is_deleted' => self::IS_DELETED['no']
        ])->one();

        if($project){
            return '项目名不能重复';
        }

        if (!$this->save()) {
            return current($this->getFirstErrors());
        }

        //记录操作日志
        OperationLog::createLog($this->id, UserInfo::$staticUserInfo->id, $this->id, OperationLog::ACTION['create'][0], '项目:' . $this->title, OperationLog::OBJECT_TYPE['project'][0]);


        return true;
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
        $this->scenario = self::SCENARIO_UPDATE;
        $res->attributes = $request;
        if (!$res->save()) {
            return current($this->getFirstErrors());
        }

        //记录操作日志
        OperationLog::createLog($this->id, UserInfo::$staticUserInfo->id, $this->id, OperationLog::ACTION['update'][0], '项目:' . $request['title'], OperationLog::OBJECT_TYPE['project'][0]);

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
        if (!$res) {
            return '没有找到要删除的对象';
        }

        $res->is_deleted = self::IS_DELETED['yes'];

        if (!$res->save(false)) {
            return current($res->getFirstErrors());
        }

        //记录操作日志
        OperationLog::createLog($this->id, UserInfo::$staticUserInfo->id, $this->id, OperationLog::ACTION['delete'][0], '项目:' . $res->title, OperationLog::OBJECT_TYPE['project'][0]);
        return true;
    }

    /**
     * 检查用户项目操作权限
     * @param UserInfo $user
     * @return bool
     * @throws
     */
    public static function checkUserProjectOperationPermission(UserInfo $user): bool
    {
       if($user->type != UserInfo::USER_TYPE['admin'][0] || $user->type != UserInfo::USER_TYPE['superuser'][0]){
           return false;
       }

       return true;
    }

}