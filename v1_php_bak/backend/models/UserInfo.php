<?php

namespace app\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;

/**
 * This is the model class for table "{{%user_info}}".
 *
 * @property string $id
 * @property string $name 名字
 * @property string $pwd
 * @property string $email 邮箱
 * @property int $is_deleted 0正常1删除
 * @property string $create_time
 * @property int $type 1普通用户2管理员
 * @property int $state 1正常2禁用3锁定
 * @property string $mobile_number 手机号
 * @property string $nick_name 昵称
 * @property string $last_login_ip 最后登陆ip
 * @property string $last_login_time
 * @property string $user_face 头像地址
 * @property string $token 访问token
 * @property string $token_expire_time token过期时间
 */
class UserInfo extends BaseModel
{

    const SCENARIO_LOGIN = 'login';
    const SCENARIO_REGISTER = 'register';
    const SCENARIO_UPDATE_PWD = 'updatePwd';
    //1正常2禁用
    const USER_STATE = ['normal' => [1, '正常'], 'disabled' => [2, '禁用']];
    //1普通用户2管理员
    const USER_TYPE = [
        'normal' => [1, '普通用户'],
        'admin' => [2, '管理员'],
        'superuser' => [3, '超级管理员'],
    ];


    public static $staticUserInfo;

    /**
     * @var string $keyword 关键字
     */
    public $keyword;

    /**
     * @var string $re_pwd 密码重复
     */
    public $re_pwd;

    /**
     * @var string $code 验证码
     */
    public $code;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_info}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'pwd'], 'required'],
            [['is_deleted', 'type', 'state'], 'integer'],
            [['create_time', 'last_login_time', 'token_expire_time'], 'safe'],
            [['name', 'pwd', 'email', 'nick_name', 'last_login_ip', 'user_face', 'token'], 'string', 'max' => 500],
            ['email', 'email'],
            [['re_pwd', 'name'], 'required', 'on' => self::SCENARIO_REGISTER],
            ['re_pwd', 'required', 'on' => self::SCENARIO_UPDATE_PWD],
            [['mobile_number'], 'string', 'max' => 11],
            [['keyword', 'code'], 'string', 'max' => 50],

        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_LOGIN] = ['name', 'pwd'];
        $scenarios[self::SCENARIO_QUERY] = ['keyword', 'ps', 'cp', 'type', 'state'];
        $scenarios[self::SCENARIO_REGISTER] = ['name', 're_pwd', 'pwd', 'nick_name', 'email'];
        $scenarios[self::SCENARIO_UPDATE_PWD] = ['re_pwd', 'pwd'];
        $scenarios[self::SCENARIO_UPDATE] = ['nick_name', 'state', 'is_deleted', 'user_face','type'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名字',
            'pwd' => 'Pwd',
            'email' => '邮箱',
            'is_deleted' => '0正常1删除',
            'create_time' => 'Create Time',
            'type' => '1普通用户2管理员',
            'state' => '1正常2禁用3锁定',
            'mobile_number' => '手机号',
            'nick_name' => '昵称',
            'last_login_ip' => '最后登陆ip',
            'last_login_time' => 'Last Login Time',
            'user_face' => '头像地址',
            'token' => '访问token',
            'token_expire_time' => 'token过期时间',
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['pwd']);
        return $fields;
    }

    /**
     * 用户登陆，返回用户信息
     * @param $username
     * @param $userPassword
     * @return UserInfo|string|null
     */
    public function login($username, $userPassword)
    {
        $userInfo = self::findOne([
            'name' => $username,
            'pwd' => self::encryptionPwd($userPassword),
            'is_deleted' => self::IS_DELETED['no']
        ]);
        if (!$userInfo) {
            return '用户名或密码错误';
        }

        //生成token以及设置过期时间
        $userInfo->last_login_ip = Yii::$app->request->getUserIP();
        $userInfo->last_login_time = date('Y-m-d H:i:s', time());
        $userInfo->token = self::createToken();
        $userInfo->token_expire_time = date('Y-m-d H:i:s', self::createExpireTime());
        if (!$userInfo->save()) {
            return '保存登陆信息失败';
        }

        $userInfo->pwd = null;
        return $userInfo;
    }

    /**
     * 创建token
     * @return string
     */
    public static function createToken()
    {
        return md5(mt_rand(1, 999999) . time());
    }

    public static function encryptionPwd(string $pwd)
    {
        return sha1(md5($pwd . $pwd) . 'myDocIsMyProjectUseVueAndPhpYii2');
    }

    /**
     * 获取过期时间
     * @param bool $isTimeStamp
     * @return false|int|string
     */
    public static function createExpireTime($isTimeStamp = false)
    {
        $time = time() + 3600;
        if (!$isTimeStamp) {
            return $time;
        } else {
            return date('Y-m-d H:i:s', $time);
        }
    }

    /**
     * 通过token获取用户
     * @param $token
     * @return UserInfo|null
     */
    public static function findUserByToken($token)
    {
        $userInfo = self::findOne([
            'token' => $token,
            'is_deleted' => self::IS_DELETED['no']
        ]);

        if (!$userInfo) {
            return null;
        }

        $currentTime = time();

        $expireTime = strtotime($userInfo['token_expire_time']);
        if ($currentTime > $expireTime) {
            return null;
        }

        //还有5分钟到期自动续期
        $fiveSecond = 5 * 60;
        $currentDiff = $expireTime - $currentTime;
        //如果只有5分钟，那么就续期
        if ($currentTime > 0 && $currentDiff < $fiveSecond) {
            $userInfo->token_expire_time = date('Y-m-d H:i:s', self::createExpireTime());
            if (!$userInfo->save()) {
                return null;
            }
        }

        return $userInfo;
    }

    /**
     * 用户注册
     *
     * @return mixed
     */
    public function reg()
    {
        if ($this->pwd !== $this->re_pwd) {
            return '两次输入的密码不一致!~';
        }

        //检查登录名或者昵称的唯一性
        $res = self::find()->where(['or', ['name' => $this->name]]);
        if ($this->email) {
            $res->orWhere(['email' => $this->email]);
        }
        if ($this->nick_name) {
            $res->orWhere(['nick_name' => $this->nick_name]);
        }

        $res = $res->one();
        if ($res) {
            return '用户名或邮箱重复';
        }

        //修改状态
        $this->state = self::USER_STATE['normal'][0];
        $this->nick_name = $this->nick_name ?: '新用户-' . $this->name;
        $this->pwd = self::encryptionPwd($this->pwd);


        $trans = self::getDb()->beginTransaction();
        try {
            if (!$this->save()) {
                throw new Exception('用户保存失败!' . current($this->getFirstErrors()));
            }

            /* $sendMail = Message::sendCodeToMail($this->email);
             if (is_string($sendMail)) {
                 throw new Exception($sendMail);
             }*/

            $trans->commit();

        } catch (Exception $e) {
            $trans->rollBack();
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 检查昵称/邮箱/名字是否重复
     * @param string $keyword
     * @return UserInfo|null
     * @throws InvalidConfigException
     */
    public static function checkReplayNickname(string $keyword)
    {
        return self::find()->where(['or', ['name' => $keyword], ['email' => $keyword], ['nick_name' => $keyword]])->one();
    }

}
