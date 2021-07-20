<?php

namespace app\controllers;

use app\behaviors\UserVerify;
use app\models\Group;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;

class GroupController extends BaseController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['userVerify'] = [
            'class' => UserVerify::class,
            'actions' => ['*'],  //设置要验证的action,如果留空或者里边放入 * ，则所有的action都要执行验证
            'excludeAction' => [], //要排除的action,在此数组内的action不执行登陆状态验证
            'projectPermission' => ['create', 'update', 'del'], //要排除的action,在此数组内的action不执行登陆状态验证

        ];
        return $behaviors;
    }

    /**
     * 创建数据
     * @return array
     */
    public function actionCreate()
    {
        $group = new Group(['scenario' => Group::SCENARIO_CREATE]);
        $group->attributes = Yii::$app->request->post();
        $res = $group->createData();
        if (is_string($res)) {
            return ['msg' => $res, 'code' => 22];
        }

        return $this->success($res);
    }

    /**
     * 更新数据
     * @return array
     */
    public function actionUpdate()
    {
        $group = new Group(['scenario' => Group::SCENARIO_UPDATE]);
        $request = Yii::$app->request->post();
        $res = $group->updateData($request);
        if (is_string($res)) {
            return $this->failed($res);
        }

        return $this->success();
    }

    /**
     * 分组列表
     * @return array
     */
    public function actionList()
    {
        $projectId = Yii::$app->request->get('projectId');
        $type = Yii::$app->request->get('type', 0);
        $pId =  Yii::$app->request->get('pId', 0);

        if (!is_numeric($projectId)) {
            return $this->failed('projectId错误');
        }

        if (!is_numeric($type)) {
            return $this->failed('type错误');
        }

        $where = ['is_deleted' => 0];
        if ($type) {
            $where['type'] = $type;
        }

        $where['project_id'] = $projectId;
        $where['p_id'] = $pId ?: 0;

        $res = Group::find()->where($where)->asArray()->all();

        //查找子分组
        $pids = array_column($res, 'id');
        $child = Group::find()->where(['IN', 'p_id', $pids])->asArray()->all();
        $child = ArrayHelper::index($child, null, 'p_id');

        foreach ($res as  &$value) {
            if (isset($child[$value['id']])) {
                $value['childs'] = $child[$value['id']];
            } else {
                $value['childs'] = [];
            }
        }

        return $this->success($res);
    }


    /**
     * 删除数据
     * @return array
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDel()
    {
        $group = new Group(['scenario' => Group::SCENARIO_DEL]);
        $group->attributes = Yii::$app->request->post();
        $res = $group->del();
        if (is_string($res)) {
            return $this->failed($res);
        }

        return ['data' => '成功'];
    }
}
