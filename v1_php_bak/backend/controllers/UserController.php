<?php

namespace app\controllers;

use app\behaviors\UserVerify;
use app\models\UserInfo;
use Yii;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;

class UserController extends BaseController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['userVerify'] = [
            'class' => UserVerify::class,
            'actions' => ['list', 'get-user-info', 'update', 'update-pwd', 'create', 'init-pwd'],  //设置要验证的action,如果留空或者里边放入 * ，则所有的action都要执行验证
            'excludeAction' => [], //要排除的action,在此数组内的action不执行登陆状态验证
            'projectPermission' => ['update-status', 'update-nickname', 'date-pwd', 'init-pwd']
        ];
        return $behaviors;
    }

    /**
     * 用户登陆
     *
     * @return array
     */
    public function actionLogin()
    {
        $params = Yii::$app->request->post();
        $user = new UserInfo(['scenario' => UserInfo::SCENARIO_LOGIN]);
        $user->attributes = $params;
        if (!$user->validate()) {
            return $this->failed(current($user->getFirstErrors()));
        }

        $userInfo = $user->login($user->name, $user->pwd);

        if (is_string($userInfo)) {

            return $this->failed($userInfo);
        }

        return $this->success($userInfo);
    }

    /**
     * 获取用户列表
     * @return array
     */
    public function actionList()
    {
        $params = Yii::$app->request->get();
        $projectId = $params['projectId'] ?? 0;
        $all = boolval($params['all'] ?? false);


        $user = new UserInfo(['scenario' => UserInfo::SCENARIO_QUERY]);
        $user->attributes = $params;
        if (!$user->validate()) {
            return $this->failed('参数错误');
        }

        $res = UserInfo::find()->alias('a')->select('
        a.id,
        a.name,
        a.email,
        a.is_deleted,
        a.create_time,
        a.type,
        a.state,
        a.mobile_number,
        a.nick_name,
        a.last_login_ip,
        a.last_login_time,
        a.user_face,
        ');
        $res->where(['a.is_deleted' => UserInfo::IS_DELETED['no']]);

        $isAdmin = false;
        if (UserInfo::$staticUserInfo &&
            in_array(UserInfo::$staticUserInfo->type, [UserInfo::USER_TYPE['superuser'][0], UserInfo::USER_TYPE['admin'][0]])) {
            $isAdmin = true;
        }

        if ($isAdmin === false) {
            $res->andWhere(['a.state' => UserInfo::USER_STATE['normal'][0]]);
        }

        if ($user->keyword) {
            $res->andWhere(['or',
                ['like', 'a.nick_name', $user->keyword . '%', false],
                ['like', 'a.name', $user->keyword . '%', false]
            ]);
        }

        if ($all === false && $projectId) {
            $res->innerJoin('user_project b', 'a.id = b.user_id')->andWhere(['b.project_id' => $projectId]);
        }

        $recordCount = $res->count();
        $res = $res->limit($user->ps)->offset(($user->cp - 1) * $user->ps)->orderBy('id DESC')->all();
        return $this->success(['count' => $recordCount, 'items' => $res]);
    }

    /**
     * 用户注册
     *
     * @return array
     */
    public function actionReg()
    {
        /**
         * 1.登录账号
         * 2.昵称
         * 3.密码
         * 4.邮箱
         */
        $params = Yii::$app->request->post();
        $user = new UserInfo(['scenario' => UserInfo::SCENARIO_REGISTER]);
        $user->attributes = $params;
        if (!$user->validate()) {
            return $this->failed(current($user->getFirstErrors()));
        }

        $userInfo = $user->reg();
        if (is_string($userInfo)) {
            return $this->failed($userInfo);
        }

        return $this->success($userInfo);
    }

    /**
     * 用户创建
     *
     * @return array
     */
    public function actionCreate()
    {
        /**
         * 1.登录账号
         * 2.昵称
         * 3.密码
         * 4.邮箱
         */

        if (false === in_array($this->userInfo->type, [UserInfo::USER_TYPE['admin'][0], UserInfo::USER_TYPE['superuser'][0]])) {
            return $this->failed('没有权限创建用户');
        }

        $params = Yii::$app->request->post();
        $user = new UserInfo(['scenario' => UserInfo::SCENARIO_REGISTER]);
        $user->attributes = $params;
        if (!$user->validate()) {
            return $this->failed(current($user->getFirstErrors()));
        }

        $userInfo = $user->reg();
        if (is_string($userInfo)) {
            return $this->failed($userInfo);
        }

        return $this->success($userInfo);
    }

    /**
     * 检查昵称是否重复
     * @param $keyword
     * @return array
     * @throws
     */
    public function actionCheckRepeat($keyword = null)
    {
        if (!$keyword) {
            return $this->failed('检查的值不能为空');
        }
        $res = UserInfo::checkReplayNickname($keyword);
        if ($res) {
            return $this->success(['name' => $res->name, 'email' => $res->email, 'nick_name' => $res->nick_name]);
        }
        return $this->success([]);
    }

    /**
     * 更新用户信息
     * @return mixed
     * @throws InvalidConfigException
     */
    public function actionUpdate()
    {
        $nickname = Yii::$app->request->post('nick_name');
        $userFace = Yii::$app->request->post('face_url');
        $userId = Yii::$app->request->post('userId');
        $state = Yii::$app->request->post('state');
        $isDeleted = Yii::$app->request->post('is_deleted');
        $userType = Yii::$app->request->post('userType');
        $dataArr = compact('nickname', 'userId', 'state', 'isDeleted', 'userFace', 'userType');
        $rules = DynamicModel::validateData($dataArr, [
            [['state', 'isDeleted', 'userId', 'userType'], 'number'],
            [['nickname', 'userFace'], 'string', 'max' => 500],
        ]);

        $oldUserData = $this->userInfo;

        if (!$rules->validate()) {
            return $this->failed(current($rules->getFirstErrors()));
        }

        //只有超级管理员可以设置用户类型
        if ($userType && $this->userInfo->type !== UserInfo::USER_TYPE['superuser'][0]) {
            return $this->failed('超级管理员才能改变用户类型');
        }

        if ($nickname && UserInfo::checkReplayNickname($nickname)) {
            return $this->failed('昵称重复或相同');
        }

        //通过传入userId判断是否管理员操作
        if ($userId && $userId != $oldUserData->id && !in_array($oldUserData->type, [UserInfo::USER_TYPE['admin'][0], UserInfo::USER_TYPE['superuser'][0]])) {
            return $this->failed('非管理员不能操作其他用户');
        }

        //判断是否是更改自己的信息
        if ($userId && $userId != $this->userInfo->id) {
            $oldUserData = UserInfo::findOne($userId);
            if (!$oldUserData) {
                return $this->failed('没有找到要更新的数据');
            }
        }

        if ($nickname && $oldUserData->nick_name != $nickname) {
            $oldUserData->nick_name = $nickname;
        }

        if ($state && $oldUserData->state != $state) {
            $oldUserData->state = $state;
        }

        if (is_numeric($isDeleted) && $oldUserData->is_deleted != $isDeleted) {
            $oldUserData->is_deleted = $isDeleted;
        }

        if ($userFace && $oldUserData->user_face != $userFace) {
            $oldUserData->user_face = $userFace;
        }

        if ($userType && $oldUserData->type != $userType) {
            $oldUserData->type = $userType;
        }

        if ($oldUserData->getDirtyAttributes()) {
            $oldUserData->setScenario(UserInfo::SCENARIO_UPDATE);
            if (!$oldUserData->save()) {
                return $this->failed('保存失败:' . current($oldUserData->getFirstErrors()));
            }
        }

        return $this->success(null, '更新完成');

    }

    /**
     * 更改用户密码
     * @return array
     * @throws
     */
    public function actionUpdatePwd(): array
    {
        $pwdSecond = Yii::$app->request->post('rePwd', '');
        if (!$pwdSecond) {
            return $this->failed('第二次新密码没有获取到');
        }

        $user = new UserInfo(['scenario' => UserInfo::SCENARIO_UPDATE_PWD]);
        $user->pwd = Yii::$app->request->post('oldPwd', '');
        $user->re_pwd = Yii::$app->request->post('newPwd', '');
        if (!$user->validate()) {
            return $this->failed(current($user->getFirstErrors()));
        }

        if ($user->re_pwd !== $pwdSecond) {
            return $this->failed('两次新密码输入不一致');
        }

        if ($user->pwd === $pwdSecond) {
            return $this->failed('密码没有更改');
        }

        //查找用户信息
        $res = UserInfo::findOne(['id' => $this->userInfo->id, 'pwd' => UserInfo::encryptionPwd($user->pwd), 'is_deleted' => UserInfo::IS_DELETED['no']]);
        if (!$res) {
            return $this->failed('获取用户信息失败');
        }

        $res->pwd = UserInfo::encryptionPwd($user->re_pwd);
        if (!$res->save()) {
            return $this->failed(current($user->getFirstErrors()));
        }

        return $this->success();
    }

    /**
     * 初始化密码
     * @return array
     */
    public function actionInitPwd()
    {
        $userId = Yii::$app->request->post('userId', '');
        if (!$userId) {
            return $this->failed('用户id不能为空');
        }

        //查找用户信息
        $res = UserInfo::findOne(['id' => $userId, 'is_deleted' => UserInfo::IS_DELETED['no']]);
        if (!$res) {
            return $this->failed('获取用户信息失败');
        }

        $res->pwd = UserInfo::encryptionPwd('123456');
        if (!$res->save()) {
            return $this->failed(current($res->getFirstErrors()));
        }

        return $this->success([], '密码重置为 123456');
    }

    /**
     * 获取用户信息
     * @return array
     */
    public function actionGetUserInfo()
    {
        $userId = $this->userInfo->id;
        $res = UserInfo::findOne($userId);
        if ($res) {
            $res = $res->toArray();
            $res['state_text'] = 0;
            $res['type_text'] = 0;
            foreach (UserInfo::USER_TYPE as $value) {
                if ($res['type'] == $value[0]) {
                    $res['type_text'] = $value[1];
                }
            }

            foreach (UserInfo::USER_STATE as $value) {
                if ($res['state'] == $value[0]) {
                    $res['state_text'] = $value[1];
                }
            }
        }
        return $this->success($res);
    }

    /**
     * 设置http请求方法
     * @return array
     */
    public function verbs()
    {
        $verbs = parent::verbs();
        $verbs['login'] = ['post'];
        $verbs['reg'] = ['post'];
        return $verbs;
    }

}
