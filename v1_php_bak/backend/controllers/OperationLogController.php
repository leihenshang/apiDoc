<?php

namespace app\controllers;

use app\behaviors\UserVerify;
use app\models\OperationLog;
use app\models\Project;
use app\models\UserProject;
use yii\db\ActiveRecord;

class OperationLogController extends BaseController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['userVerify'] = [
            'class' => UserVerify::class,
            'actions' => ['*'],  //设置要验证的action,如果留空或者里边放入 * ，则所有的action都要执行验证
            'excludeAction' => [], //要排除的action,在此数组内的action不执行登陆状态验证
        ];
        return $behaviors;
    }

    /**
     * 获取操作日志列表
     * @param int|null $object_id
     * @param string $type
     * @param int $ps
     * @param int $cp
     * @return OperationLog[]|Project[]|UserProject[]|array|ActiveRecord[]
     */
    public function actionList(int $object_id = null, string $type = '', int $ps = 10, int $cp = 1)
    {
        if (!$object_id) {
            return $this->failed('对象id不能为空');
        }
        if (!$type) {
            return $this->failed('查询类型不能为空');
        }

        $typeArr = explode(',',$type);
        if(!$typeArr){
            return $this->failed('查询类型解析错误');
        }

        $offset = ($cp - 1) * $ps;

        $res =  OperationLog::find()->alias('a')
            ->select('a.*,b.nick_name')
            ->leftJoin('user_info b','a.user_id = b.id')
            ->where([
            'a.is_deleted' => OperationLog::IS_DELETED['no'],
            'a.project_id' => $object_id,
            'a.type' => $typeArr
        ])->limit($ps)->offset($offset)->orderBy('a.id DESC')->asArray()->all();

        return $this->success($res);
    }

}