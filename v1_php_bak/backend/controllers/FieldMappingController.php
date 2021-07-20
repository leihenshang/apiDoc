<?php

namespace app\controllers;

use app\behaviors\UserVerify;
use app\models\FieldMapping;
use Yii;

class FieldMappingController extends BaseController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['userVerify'] = [
            'class' => UserVerify::class,
            'actions' => ['*'],  //设置要验证的action,如果留空或者里边放入 * ，则所有的action都要执行验证
            'excludeAction' => [],//要排除的action,在此数组内的action不执行登陆状态验证
            'projectPermission' => ['create', 'update', 'delete', 'restore'],
        ];
        return $behaviors;
    }

    /**
     * 创建数据
     * @return array
     */
    public function actionCreate()
    {
        $model = new FieldMapping(['scenario' => FieldMapping::SCENARIO_CREATE]);
        $model->attributes = Yii::$app->request->post();
        if (!$model->validate()) {
            return $this->failed(current($model->getFirstErrors()));
        }
        $res = $model->createData();
        if (is_string($res)) {
            return $this->failed($res);
        }

        return $this->success();
    }

    /**
     * 更新数据
     * @return array
     * @throws
     */
    public function actionUpdate()
    {
        $model = new FieldMapping(['scenario' => FieldMapping::SCENARIO_UPDATE]);
        $request = Yii::$app->request->post();
        $model->attributes = $request;
        if (!$model->validate()) {
            return $this->failed(current($model->getFirstErrors()));
        }

        $res = $model->updateData();
        if (is_string($res)) {
            return $this->failed('失败');
        }

        return $this->success();
    }

    /**
     * api列表
     * @return array
     */
    public function actionList()
    {
        $projectId = Yii::$app->request->get('projectId');
        if (!$projectId) {
            return $this->failed('projectId不能为空');
        }
        $res = new FieldMapping();
        $res = $res->dataList($projectId);
        return $this->success($res);
    }


    /**
     * 删除数据
     * @return array
     * @throws
     */
    public function actionDelete()
    {
        $model = new FieldMapping(['scenario' => FieldMapping::SCENARIO_DELETE]);
        $model->attributes = Yii::$app->request->post();
        if (!$model->validate()) {
            return $this->failed(current($model->getFirstErrors()));
        }

        $res = $model->deleteData();
        if (is_string($res)) {
            return $this->failed('失败');
        }

        return $this->success();
    }


}