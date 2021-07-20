<?php

namespace app\controllers;

use app\behaviors\UserVerify;
use app\models\BaseModel;
use app\models\Notification;
use app\models\Project;
use app\models\UserInfo;
use app\models\UserProject;
use Throwable;
use Yii;
use yii\db\StaleObjectException;

class ProjectController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['userVerify'] = [
            'class' => UserVerify::class,
            'actions' => ['*'],  //设置要验证的action,如果留空或者里边放入 * ，则所有的action都要执行验证
            'excludeAction' => [], //要排除的action,在此数组内的action不执行登陆状态验证
            'projectPermission' => ['create', 'update', 'del', 'add-user', 'del-user', 'set-leader', 'quit-project', 'set-permission']
        ];
        return $behaviors;
    }


    public function actionTest()
    {
        $res = Project::find()->where(['id' => '1'])->one();
        return ['data' => $res];
    }

    /**
     * 创建数据
     * @return array
     */
    public function actionCreate()
    {
        $project = new Project(['scenario' => Project::SCENARIO_CREATE]);
        $project->attributes = Yii::$app->request->post();
        $res = $project->createData();
        if (is_string($res)) {
            return ['code' => 22, 'msg' => $res];
        }

        return ['data' => '成功'];
    }

    /**
     * 更新数据
     * @return array
     */
    public function actionUpdate()
    {
        $project = new Project(['scenario' => Project::SCENARIO_DEL]);
        $request = Yii::$app->request->post();
        $res = $project->updateData($request);
        if (is_string($res)) {
            return $this->failed($res);
        }

        return ['data' => '成功'];
    }

    /**
     * 项目列表
     * @return array
     */
    public function actionList(): array
    {
        $ps = Yii::$app->request->get('ps');
        $cp = Yii::$app->request->get('cp');
        $offset = ($cp - 1) * $ps;

        if ($this->userInfo->type === UserInfo::USER_TYPE['normal'][0]) {
            $res = UserProject::find()->alias('a')
                ->select('b.*')
                ->innerJoin('project b', 'a.project_id = b.id')
                ->where([
                    'b.is_deleted' => UserInfo::IS_DELETED['no'],
                    'a.is_deleted' => UserInfo::IS_DELETED['no'],
                    'a.user_id' => $this->userInfo->id
                ]);
            $resCount = $res->count();
        } else {
            $res = Project::find()->where(['is_deleted' => 0])->limit($ps)->offset($offset)->orderBy('create_time desc');
            $resCount = $res->count();
        }

        return $this->success( ['count' => $resCount, 'items' => $res->asArray()->all()]);
    }

    /**
     * 项目详情
     * @return array
     */
    public function actionDetail(): array
    {
        $id = Yii::$app->request->get('id', null);
        $res = Project::findOne($id);
        return $this->success($res);
    }

    /**
     * 删除数据
     * @return array
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete(): array
    {
        if (Project::checkUserProjectOperationPermission($this->userInfo)) {
            return $this->failed('非管理员不能执行删除');
        }

        $project = new Project(['scenario' => Project::SCENARIO_DEL]);
        $project->attributes = Yii::$app->request->post();
        $res = $project->del();
        if (is_string($res)) {
            return $this->failed($res);
        }

        return ['data' => '成功'];
    }

    /**
     * 获取项目用户列表
     * @return array
     */
    public function actionProjectUser(): array
    {
        $id = Yii::$app->request->get('id');
        if (!$id) {
            return $this->failed('获取id参数失败');
        }

        $res = UserProject::find()->alias('a')
            ->innerJoin('user_info b', 'a.user_id = b.id')
            ->select('b.*,a.permission,a.is_leader')
            ->where([
                'a.project_id' => $id,
                'a.is_deleted' => UserProject::IS_DELETED['no']
            ])
            //不显示超级用户
            ->andWhere(['NOT IN', 'b.type', [UserInfo::USER_TYPE['admin'][0], UserInfo::USER_TYPE['superuser'][0]]])
            ->asArray()->all();

        $res = array_map(function ($a) {
            if (isset($a['pwd'])) {
                $a['pwd'] = null;
            }
            return $a;
        }, $res);

        return $this->success($res);
    }

    /**
     * 为项目添加用户
     * @return array
     */
    public function actionAddUser()
    {
        $params = Yii::$app->request->post();
        $userProject = new UserProject(['scenario' => UserProject::SCENARIO_CREATE]);
        $userProject->attributes = $params;
        if (!$userProject->validate()) {
            return $this->failed(current($userProject->getFirstErrors()));
        }

        $project = Project::findOne($userProject->project_id);
        if (!$project) {
            return $this->failed('没有找到项目');
        }

        //检查重复添加
        $res = UserProject::find()->where([
            'user_id' => $userProject->user_id,
            'project_id' => $userProject->project_id
        ])->one();

        if ($res) {

            if (!$res->is_deleted) {
                return $this->failed('请勿重复添加');
            }

            $res->is_deleted = Project::IS_DELETED['no'];
            if (!$res->save(false)) {
                return $this->failed(current($res->getFirstErrors()));
            }
        } else {
            if (!$userProject->save(false)) {
                return $this->failed(current($userProject->getFirstErrors()));
            }
        }
        //创建消息提示
        Notification::createNotice($userProject->user_id, '已经被邀请到项目:' . $project->title . '中,请进入项目列表中查看', '项目邀请');
        return $this->success();
    }


    /**
     * 设置团队leader
     * @return array
     */
    public function actionSetLeader()
    {
        $userId = Yii::$app->request->post('userId', null);
        $projectId = Yii::$app->request->post('projectId', null);
        $cancel = Yii::$app->request->post('cancel', 0);

        //判断只有管理员可知设置团队leader
        if ($this->userInfo->type != UserInfo::USER_TYPE['admin'][0]) {
            return $this->failed('非管理员禁止操作');
        }

        if (!$userId || !$projectId) {
            return $this->failed('必须传入userId以及projectId');
        }

        $userProject = UserProject::findOne(['user_id' => $userId, 'project_id' => $projectId]);
        if (!$userProject) {
            return $this->failed('没有找到要设置的用户');
        }

        $project = Project::findOne($projectId);
        if (!$project) {
            return $this->failed('没有找到项目');
        }

        if (intval($cancel) === 1) {
            $userProject->is_leader = UserProject::IS_LEADER['no'];
        } else {
            $userProject->is_leader = UserProject::IS_LEADER['yes'];
        }

        if (!$userProject->save()) {
            return $this->failed('设置团队leader失败');
        }

        //创建消息提示
        Notification::createNotice($userProject->user_id, '已经被设置为项目:' . $project->title . '管理者,请进入项目列表中查看', '项目通知');
        return $this->success('成功');
    }

    /**
     * 退出项目
     * @return array
     */
    public function actionQuitProject()
    {
        $userId = Yii::$app->request->post('userId', null);
        $projectId = Yii::$app->request->post('projectId', null);

        if (!$userId || !$projectId) {
            return $this->failed('必须传入userId以及projectId');
        }

        //检查操作权限
        $checkRes = Project::checkUserProjectOperationPermission($this->userInfo);
        if (!$checkRes) {
            return $this->failed('非管理员禁止操作');
        }

        $userProject = UserProject::find()->where([
            'user_id' => $userId,
            'is_deleted' => UserProject::IS_DELETED['no'],
            'project_id' => $projectId
        ])->one();
        if (!$userProject) {
            return $this->failed('没有找到要设置的用户');
        }

        $project = Project::findOne($projectId);
        if (!$project) {
            return $this->failed('没有找到项目');
        }

        $userProject->is_deleted = UserProject::IS_DELETED['yes'];
        if (!$userProject->save()) {
            return $this->failed('退出项目失败');
        }

        Notification::createNotice($userProject->user_id, '已经被管理员移出项目:' . $project->title . '', '项目通知');
        return $this->success([]);
    }


    /**
     * 设置权限
     * @return array
     */
    public function actionSetPermission()
    {
        $userId = Yii::$app->request->post('userId');
        $projectId = Yii::$app->request->post('projectId');
        $permission = Yii::$app->request->post('permission');

        if (!$userId || !$projectId || !$permission) {
            return $this->failed('必须传入userId以及projectId,permission');
        }

        $permissionRange = array_merge(array_column(UserProject::PERMISSION, 0), [6]);
        if (!in_array($permission, $permissionRange)) {
            return $this->failed('设置权限值不正确');
        }

        //检查操作权限
        $checkRes = Project::checkUserProjectOperationPermission($this->userInfo);
        if (!$checkRes) {
            return $this->failed('非管理员禁止操作');
        }

        $userProject = UserProject::find()->where([
            'user_id' => $userId,
            'is_deleted' => UserProject::IS_DELETED['no'],
            'project_id' => $projectId
        ])->one();
        if (!$userProject) {
            return $this->failed('没有找到要设置的用户');
        }

        $project = Project::findOne($projectId);
        if (!$project) {
            return $this->failed('没有找到项目');
        }

        $userProject->permission = $permission;
        if (!$userProject->save()) {
            return $this->failed('设置权限失败');
        }

        Notification::createNotice($userProject->user_id, '对项目:' . $project->title . '的权限已经修改为' . $permission, '项目通知');

        return $this->success([]);
    }

    /**
     * 检查可操作权限
     * @return array
     */
    public function actionCheckOperationPermission(): array
    {
        $projectId = Yii::$app->request->post('projectId', null);

        if (!$projectId) {
            return $this->failed('必须传入projectId');
        }

        //检查操作权限
        $checkRes = Project::checkUserProjectOperationPermission($this->userInfo);
        if (!$checkRes) {
            return $this->failed('非管理员禁止操作');
        }

        return $this->success();
    }

    /**
     * 获取用户对项目的操作权限
     * @return array
     */
    public function actionGetProjectOperationPermission()
    {
        $projectId = Yii::$app->request->get('projectId', null);

        if (!$projectId) {
            return $this->failed('必须传入projectId');
        }

        if ($this->userInfo->type == UserInfo::USER_TYPE['admin'][0]) {
            return $this->success(UserProject::PERMISSION['read'][0] + UserProject::PERMISSION['write'][0]);
        }

        //检查操作权限
        $permission = UserProject::find()
            ->where(['userId' => $this->userInfo->id])
            ->where(['project_id' => $projectId])
            ->where(['is_deleted' => BaseModel::IS_DELETED['no']])->one();
        if (!$permission) {
            return $this->failed('获取用户-项目权限数据失败，不允许操作');
        }

        return $this->success($permission->permission);
    }

}