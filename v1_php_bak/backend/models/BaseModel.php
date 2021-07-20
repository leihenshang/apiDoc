<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 基础模型
 * Class BaseModel
 * @property int $ps
 * @property int $cp
 * @property int $offset
 * @package app\models
 */
class BaseModel extends ActiveRecord
{
    const IS_DELETED = ['yes' => 1, 'no' => 0];

    //默认场景验证
    const SCENARIO_CREATE = 'create';
    const SCENARIO_DELETE = 'delete';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_QUERY = 'query';
    const SCENARIO_DETAIL = 'detail';
    const SCENARIO_LIST = 'list';


    /**
     * @var int $ps 分页
     */
    public  $ps = 10;
    /**
     * @var int $cp 当前页
     */
    public  $cp = 1;


    public function getOffset()
    {
        return ($this->cp - 1) * $this->ps;
    }

    public static function getOffsetByPageParam(int $ps,int $cp)
    {
        return ($cp - 1) * $ps;
    }

    /**
     * 成功
     * @param mixed $data 数据
     * @param string $msg 消息
     * @param int $code 代码
     * @return array
     */
    public static function success($data=null,string $msg = '',int $code = 200)
    {
        return ['data' => $data, 'code' => $code, 'msg' => $msg];
    }

    /**
     * 失败
     * @param string $msg 消息
     * @param int $code 代码
     * @return array
     */
    public static function failed(string $msg = 'failed',int $code = 14)
    {
        return ['data' => null, 'code' => $code, 'msg' => $msg];
    }

}