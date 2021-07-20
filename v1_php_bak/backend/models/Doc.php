<?php

namespace app\models;

/**
 * This is the model class for table "{{%doc}}".
 *
 * @property int $id id
 * @property int $is_deleted 0正常1删除
 * @property int $user_id
 * @property string $title 标题
 * @property string $content 文档内容
 * @property int $state 0正常1审核中2禁用
 * @property int $group_id
 * @property string $create_time
 * @property string $update_time
 * @property int $view_count
 * @property int $like_count
 * @property int $project_id
 * @property int $is_top
 * @property int $priority
 */
class Doc extends BaseModel
{
    //0正常1审核中2禁用
    const STATE = [
        'normal' => [0, '正常'],
        'audit' => [1, '审核中'],
        'disable' => [2, '禁用'],
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%doc}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_deleted', 'user_id', 'state', 'group_id', 'view_count', 'like_count', 'project_id', 'is_top', 'priority'], 'integer'],
            [['title', 'content'], 'required', 'on' => self::SCENARIO_CREATE],
            [['content'], 'string'],
            [['create_time', 'update_time'], 'safe'],
            [['title'], 'string', 'max' => 100],
            [['group_id', 'project_id'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_LIST]],
            ['project_id', 'integer', 'min' => 1],
            ['id', 'integer'],
            ['id', 'required', 'on' => [self::SCENARIO_DELETE, self::SCENARIO_DETAIL]],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'is_deleted' => '0正常1删除',
            'user_id' => 'User ID',
            'title' => '标题',
            'content' => '文档内容',
            'state' => '0正常1审核中2禁用',
            'group_id' => 'Group ID',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'view_count' => 'View Count',
            'like_count' => 'Like Count',
            'is_top' => '置顶',
            'priority' => '置顶优先级',
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['group_id', 'title', 'content', 'project_id'];
        $scenarios[self::SCENARIO_DELETE] = ['id'];
        $scenarios[self::SCENARIO_UPDATE] = ['state', 'title', 'content', 'group_id', 'id', 'is_top'];
        $scenarios[self::SCENARIO_LIST] = [];
        return $scenarios;
    }

    public function createData()
    {
        if (!$this->save()) {
            return '保存数据失败:' . current($this->getFirstErrors());
        }

        return $this->toArray();
    }

    public function deleteData()
    {
        $res = self::findOne($this->id);
        if (!$res) {
            return '没有找到数据';
        }

        $res->is_deleted = self::IS_DELETED['yes'];
        if (!$res->save(false)) {
            return '删除数据失败:' . current($this->getFirstErrors());
        }

        return $this->toArray();
    }

    /**
     * 更新数据
     * @param $request
     * @return array|string
     */
    public function updateData($request)
    {
        $res = self::findOne($this->id);
        if (!$res) {
            return '没有找到数据';
        }

        $res->attributes = $request;
        if ($res->save(false)) {
            return $res->toArray();
        }

        return '更新失败,' . current($res->getFirstErrors());
    }

    /**
     * 文档详情
     * @return Doc|string
     */
    public function detail()
    {
        $res = self::find()->alias('a')
            ->select('a.*,b.nick_name')
            ->leftJoin('user_info b', 'a.user_id = b.id')
            ->where(['a.id' => $this->id])
            ->asArray()
            ->one();

        //获取分组信息
        $groupId = $res['group_id'] ?? 0;
        if ($groupId) {
            $group = Group::find()->where(['id' => $groupId])->one();
            if ($group && $group['p_id']) {
                $res['group_id_second'] = $res['group_id'];
                $res['group_id'] = $group['p_id'];
            }
        }

        if (!$res) {
            return '没有找到数据';
        }

        return $res;
    }

    /**
     * 文档列表
     * @param int $projectId
     * @param int $groupId
     * @param int $ps
     * @param int $cp
     * @param int $isDelete
     * @param string $keyword
     * @param int $isTop
     * @return array|string
     */
    public function dataList(
        int $projectId,
        $groupId = 0,
        $ps = 10,
        $cp = 1,
        $isDelete = 0,
        $keyword = '',
        $isTop = null
    )
    {

        $where = [];
        if ($groupId > 0) {
            $where = ['a.group_id' => $groupId];
        }

        $where['a.project_id'] = $projectId;
        $where['a.is_deleted'] = $isDelete == 1 ? self::IS_DELETED['yes'] : self::IS_DELETED['no'];
        $where['a.state'] = self::STATE['normal'];
        if (is_numeric($isTop)) {
            $where['a.is_top'] = $isTop;
        }

        if ($where['a.is_deleted'] === self::IS_DELETED['yes']) {
            unset($where['a.group_id']);
        }

        $query = self::find()->alias('a')->where($where)
            ->leftJoin('user_info b', 'a.user_id  = b.id')
            ->select('a.*,b.nick_name');
        if ($keyword) {
            $query->andWhere(['like', 'a.title', "{$keyword}%", false]);
        }

        $data['count'] = $query->count();
        $data['items'] = $query
            ->offset(($cp - 1) * $ps)
            ->orderBy('a.id DESC')
            ->limit($ps)
            ->asArray()
            ->all();

        return $data;
    }
}
