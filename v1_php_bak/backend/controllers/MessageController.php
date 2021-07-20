<?php

namespace app\controllers;

use app\behaviors\UserVerify;
use app\models\Api;
use app\models\Message;
use Yii;
use yii\validators\EmailValidator;

class MessageController extends BaseController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['userVerify'] = [
            'class' => UserVerify::class,
            'actions' => [],  //设置要验证的action,如果留空或者里边放入 * ，则所有的action都要执行验证
            'excludeAction' => [], //要排除的action,在此数组内的action不执行登陆状态验证
        ];
        return $behaviors;
    }

    /**
     * 发送验证码
     * @param string $email 个数
     * @return array
     */
    public function actionSend($email = '')
    {

        $validate = new EmailValidator();
        if(!$validate->validate($email)){
            return $this->failed('错误的邮箱地址');
        }

        return $this->success((new Message())->sendCodeToMail($email));
    }
}