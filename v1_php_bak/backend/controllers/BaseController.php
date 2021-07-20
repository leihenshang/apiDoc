<?php

namespace app\controllers;

use app\models\UserInfo;
use yii\rest\Controller;

/**
 * Class BaseController
 * @property UserInfo $userInfo 通过用户验证行为类注入到基类中
 * @package app\controllers
 */
class BaseController extends Controller
{
    /**
     * @param null $data
     * @param string $msg
     * @param int $code
     * @return array
     */
    public function success($data = null, $msg = 'success', $code = 200): array
    {
        return ['data' => $data, 'msg' => $msg, 'code' => $code];
    }

    /**
     * @param string $msg
     * @param int $code
     * @param null $data
     * @return array
     */
    public function failed($msg = 'failed', $code = 14, $data = null): array
    {
        return $this->success($data, $msg, $code);
    }

}