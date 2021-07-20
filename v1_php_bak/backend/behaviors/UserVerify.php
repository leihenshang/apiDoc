<?php
/**
 * @DateTime 2019.6.5
 */

namespace app\behaviors;

use app\models\UserInfo;
use app\models\UserProject;
use Yii;
use yii\base\Behavior;
use yii\base\ErrorException;
use yii\base\InvalidArgumentException;
use yii\base\UserException;
use yii\web\Controller;
use yii\web\UnauthorizedHttpException;

/**
 * 用户登陆状态验证行为
 * Class UserVerify
 * @package api\behaviors
 */
class UserVerify extends Behavior
{

    /*
    使用方法：
     在控制器中执行行为绑定，如果基类中有行为，先调用基类中的行为方法

    public function behaviors()
     {
         $behaviors = parent::behaviors();
             $behaviors['userVerify'] = [
                 'class' => UserVerify::className(),
                 'actions' => [],  //设置要验证的action,里边放入 * ，则所有的action都要执行验证
                 'excludeAction' => ['list', 'detail', 'listbyids'], //要排除的action,在此数组内的action不执行登陆状态验证
             ];
         return $behaviors;
     }

    */

    //=================验证用户是否登陆==============
    /**
     * @var UserInfo $userInfo 用户信息
     */
    public $userInfo = null;
    public $projectUser = null;

    //需要进行验证的action数组
    public $actions = [];


    //排除验证的action数组
    public $excludeAction = [];
    //验证的结果，未验证成功为false,验证成功为true
    private $isValid = true;

    //=================验证前后台接口访问权限==============
    //前台接口
    public $frontAction = [];
    //后台接口
    public $backendAction = [];

    //前台用户类型
    public $frontUserType = [601];
    //后台用户类型
    public $backendUserType = [600];

    //==============检擦项目操作权限============================
    public $projectPermission = [];

    public function events()
    {
        //设置触发控制器的 beforeAction 事件，使其在调用action前就执行验证
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    /**
     * beforeAction 重写调用action前执行的方法
     * @param $event object
     * @return bool
     * @throws
     */
    public function beforeAction($event)
    {
        $loginResult = $this->checkLogin($event);
        if ($loginResult !== true) {
            return false;
        }

        $checkPermissionResult = $this->checkPermission($event);
        if ($checkPermissionResult !== true) {
            return false;
        }

        $checkProjectOperationPermission = $this->checkProjectOperationPermission($event);
        if ($checkProjectOperationPermission !== true) {
            return false;
        }

        return true;
    }

    /**
     * 检查用户对项目的操作权限
     * @param $event
     * @return bool
     * @throws UnauthorizedHttpException
     */
    public function checkProjectOperationPermission($event)
    {
        //拿到action的id,比如访问的是actionIndex,此处获取的actionId是 index
        $action = $event->action->id;
        if (!in_array($action, $this->projectPermission)) {
            return true;
        }

        if (!$this->userInfo) {
            throw new UnauthorizedHttpException('没有用户登陆信息', 34);
        }

        if ($this->userInfo->type == UserInfo::USER_TYPE['admin'][0] || $this->userInfo->type == UserInfo::USER_TYPE['superuser'][0]) {
            return true;
        }

        if (Yii::$app->request->isPost) {
            $projectId = Yii::$app->request->getBodyParam('projectId', 0) ?: Yii::$app->request->getBodyParam('project_id', 0);
        } else {
            $projectId = Yii::$app->request->get('projectId', 0) ?: Yii::$app->request->get('project_id', 0);
        }

        if (!$projectId) {
            throw new UnauthorizedHttpException('没有项目id', 22);
        }

        if (in_array($action, $this->projectPermission)) {
            $this->projectUser = UserProject::findOne(['user_id' => $this->userInfo->id, 'is_deleted' => UserProject::IS_DELETED['no'], 'project_id' => $projectId]);
            if (!$this->projectUser) {
                throw new UnauthorizedHttpException('没有该项目的操作权限', 34);
            }


            if ($this->projectUser->permission == UserProject::PERMISSION['read'][0]) {
                throw new UnauthorizedHttpException('没有操作权限', 22);
            }
        }

        return true;
    }

    /**
     * 检查用户前后台接口的访问权限
     * @param $event
     * @return bool
     * @throws ErrorException
     * @throws UnauthorizedHttpException
     * @throws UserException
     */
    public function checkPermission($event)
    {
        $action = $event->action->id;
        if (!is_array($this->frontAction) || !is_array($this->backendAction)) {
            throw new InvalidArgumentException('参数错误,action参数以及excludeAction参数必须为array', 14);
        }

        //如果没有设置参数的话则不进行验证
        if (empty($this->frontAction) && empty($this->backendAction)) {
            return true;
        }

        if (!$this->userInfo) {
            throw new UnauthorizedHttpException('没有用户登陆信息', 34);
        }

        $userInfo = $this->userInfo;
        if (!$userInfo) {
            throw new UnauthorizedHttpException('没有获得用户信息', 34);
        }

        if (array_intersect($this->frontAction, $this->backendAction)) {
            throw new ErrorException('动作规则前台和后台不能重复项', 21);
        }

        if (array_intersect($this->frontUserType, $this->backendUserType)) {
            throw new ErrorException('设置的前台后台用户类型有重复项', 21);
        }

        //前台用户验证
        if ($this->frontAction) {

            if (!$this->checkLinearArray($this->frontAction) || !$this->checkLinearArray($this->frontUserType)) {
                throw new InvalidArgumentException('设置的frontAction和frontUserType必须为一维数组', 14);
            }

            if (!in_array($action, $this->frontAction) || !in_array($userInfo->type, $this->frontUserType)) {
                throw new UserException('访问接口与用户类型不匹配，该用户可能只能在前台登陆', 34);
            }

        }

        //后台用户验证
        if ($this->backendAction) {

            if (!$this->checkLinearArray($this->backendAction) || !$this->checkLinearArray($this->backendUserType)) {
                throw new InvalidArgumentException('设置的backendAction和backendAction必须为一维数组', 14);
            }

            if (!in_array($action, $this->backendAction) || !in_array($userInfo->UserTypeId, $this->backendAction)) {
                throw new UserException('访问接口与用户类型不匹配，该用户可能只能在后台登陆', 34);
            }

        }

        return true;
    }

    /**
     * 检查用户是否登陆
     * @param $event
     * @return bool
     * @throws UserException
     */
    public function checkLogin($event)
    {
        //拿到action的id,比如访问的是actionIndex,此处获取的actionId是 index
        $action = $event->action->id;
        $token = $this->getTokenByRequest();
        $userInfo = UserInfo::findUserByToken($token);

        if (!is_array($this->actions) || !is_array($this->excludeAction)) {
            throw new InvalidArgumentException('参数错误,action参数以及excludeAction参数必须为array', 14);
        }

        //如果没有设置验证参数则不进行任何验证
        if (empty($this->actions) && empty($this->excludeAction)) {
            return true;
        }

        //如果设置的actions中有 * 号的话代表所有的action都要进行验证
        if (in_array('*', $this->actions)) {
            $this->isValid = !$userInfo ? false : true;

        } else {
            //单个进行验证
            if (in_array($action, $this->actions)) {

                if (!$this->checkLinearArray($this->actions)) {
                    throw new InvalidArgumentException('参数错误,action参数必须为一维array', 14);
                }

                $this->isValid = !$userInfo ? false : true;
            }
        }

        //如果设置了排除的action，那么就排除登陆检查
        if (!empty($this->excludeAction) && $this->checkLinearArray($this->excludeAction) && in_array($action, $this->excludeAction)) {
            $this->isValid = true;
        }

        if (!$this->isValid) {
            throw new UserException('没有找到用户信息或登陆超时，请重新登陆试试', 34);
        }

        $this->userInfo = $userInfo;
        UserInfo::$staticUserInfo = $userInfo;
        if ($this->userInfo) {
            //检查过期时间，如果过期时间还剩余5分钟则续期1小时
            $expireTime = strtotime($userInfo->token_expire_time);
            if ($expireTime < 5 * 60) {
                $userInfo->token_expire_time = date('Y-m-d H:i:s', $expireTime + 60 * 60);
                $userInfo->save(false);
            }

        }

        return true;
    }

    /**
     *  获取用户accessToken的方法
     * @param string $method http请求方法 get|post
     * @param string $tokenName token字段名称
     * @return array|mixed
     */
    public function getTokenByRequest(string $method = '', string $tokenName = 'token')
    {
        $request = Yii::$app->request;
        //判断用户请求方法，get只能使用get方法，post只拿post方法的token
        if ($request->isGet) {
            $method = 'get';
        } elseif ($request->isPost) {
            $method = 'post';
        }

        if (empty($method) || !in_array($method, ['get', 'post'])) {
            $token = $request->get('token') ?: $request->post('token');
            if ($token) {
                return $token;
            }
            //如果没有拿到 accessToken 就去拿 AccessToken  （首字母大小写的区别)
            return $request->get($tokenName) ?: $request->post($tokenName);
        }

        if ($method === 'get') {
            $token = $request->get('token');
            if ($token) {
                return $token;
            }
            return $request->get($tokenName);
        } else {
            $token = $request->post('token');
            if ($token) {
                return $token;
            }
            return $request->post($tokenName);
        }
    }

    /**
     * 检查是否是一维数组
     * @param $arr
     * @return bool
     */
    public function checkLinearArray($arr)
    {
        if (!$arr || !is_array($arr)) {
            return false;
        }

        if (count($arr) !== count($arr, 1)) {
            return false;
        }

        return true;

    }
}
