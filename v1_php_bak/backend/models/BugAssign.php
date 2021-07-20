<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%bug_assign}}".
 *
 * @property int $id
 * @property int $bug_id bug id
 * @property int $from_user_id 来源用户
 * @property int $to_user_id 目标用户
 * @property string|null $comment 备注
 * @property int $status 1待解决2已解决3不处理
 * @property string $is_deleted 0正常1删除
 * @property string|null $create_time 创建时间
 */
class BugAssign extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bug_assign}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bug_id'], 'required'],
            [['bug_id', 'from_user_id', 'to_user_id', 'status'], 'integer'],
            [['create_time'], 'safe'],
            [['comment'], 'string', 'max' => 1000],
            [['is_deleted'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bug_id' => 'bug id',
            'from_user_id' => '来源用户',
            'to_user_id' => '目标用户',
            'comment' => '备注',
            'status' => '1待解决2已解决3不处理',
            'is_deleted' => '0正常1删除',
            'create_time' => '创建时间',
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = [
            'to_user_id',
            'from_user_id',
            'comment',
            'status',
            'bug_id',
        ];
        return $scenarios;
    }

    /**
     * 指派
     * @param int $userId
     * @param int $bugId
     * @param int $toUserId
     * @param int $status
     * @param string|null $comment
     * @return array
     */
    public function assign(int $userId, int $bugId, int $toUserId, int $status, string $comment = null)
    {
        //修改bug实体状态
        $bug = Bug::findOne($bugId);
        if(!$bug){
            return ['没有找到bug数据', null];
        }

        if ($bug->status != $status) {
            $bug->status = $status;
             if(!$bug->save()){
                 return ['保存bug数据失败', null];
             }
        }

        $this->from_user_id = $userId;
        $this->to_user_id = $toUserId;
        $this->bug_id = $bugId;
        $this->status = $status;
        $this->comment = $comment;

        if (!$this->save()) {
            return ['保存指派记录失败-' . current($this->getFirstErrors()), null];
        }

        return [null, $this];
    }

    public function dataList(
        int $bugId,
        string $sortType = "DESC",
        string $orderBy = 'create_time',
        int $cp = 1,
        int $ps = 10
    )
    {
        if (!in_array($orderBy, ['create_time', 'id',  'status'])) {
            $orderBy = 'create_time';
        }

        $sort = strtoupper($sortType) === 'DESC' ? SORT_DESC : SORT_ASC;
        $query = self::find()->where(['bug_id' => $bugId])->orderBy([$orderBy => $sort])->limit($ps)->offset(self::getOffsetByPageParam($ps, $cp));

        $count = $query->count();
        $res = $query->asArray()->all();

        //获取userid
        $userId = array_unique(ArrayHelper::getColumn($res,'from_user_id') + ArrayHelper::getColumn($res,'to_user_id'));
        $userInfo = $userId ? UserInfo::find()->select('nick_name,id')->where(['id' => $userId])->indexBy('id') ->all() : null;

        foreach ($res as &$v) {
            if (isset($userInfo[$v['from_user_id']])) {
                $v['from_user_info'] = $userInfo[$v['from_user_id']];
            } else {
                $v['from_user_info'] = null;
            }

            if (isset($userInfo[$v['to_user_id']])) {
                $v['to_user_info'] = $userInfo[$v['to_user_id']];
            } else {
                $v['to_user_info'] = null;
            }
        }

        return ['count' => $count, 'items' => $res];
    }

}
