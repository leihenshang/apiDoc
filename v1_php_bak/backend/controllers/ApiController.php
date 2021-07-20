<?php

namespace app\controllers;

use app\behaviors\UserVerify;
use app\models\Api;
use app\models\Doc;
use app\models\Group;
use app\models\OperationLog;
use Throwable;
use Yii;
use yii\base\Exception;

class ApiController extends BaseController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['userVerify'] = [
            'class' => UserVerify::class,
            'actions' => ['*'],  //设置要验证的action,如果留空或者里边放入 * ，则所有的action都要执行验证
            'excludeAction' => [], //要排除的action,在此数组内的action不执行登陆状态验证,
            'projectPermission' => ['update', 'del', 'create', 'restore']
        ];
        return $behaviors;
    }

    /**
     * 创建数据
     * @return array
     */
    public function actionCreate()
    {
        $api = new Api(['scenario' => Api::SCENARIO_CREATE]);
        $api->attributes = Yii::$app->request->post();
        $res = $api->createData();
        if (is_string($res)) {
            return $this->failed($res);
        }

        return $this->success($res);
    }

    /**
     * 更新数据
     * @return array
     */
    public function actionUpdate()
    {
        $api = new Api(['scenario' => Api::SCENARIO_UPDATE]);
        $request = Yii::$app->request->post();
        $res = $api->updateData($request);
        if (is_string($res)) {
            return $this->failed($res);
        }

        return $this->success($res);
    }

    /**
     * api列表
     * @return array
     */
    public function actionList()
    {
        $projectId = Yii::$app->request->get('projectId', 0);
        $groupId = Yii::$app->request->get('groupId', 0);
        $isDeleted = Yii::$app->request->get('isDeleted', 0);
        $keyword = Yii::$app->request->get('keyword', 0);
        if (!$projectId) {
            return ['code' => 22, 'msg' => '没有projectId'];
        }

        if (!is_numeric($isDeleted)) {
            return $this->failed('is_deleted参数错误');
        }

        $res = new Api(['scenario' => Api::SCENARIO_LIST]);
        $res = $res->dataList($projectId, Yii::$app->request->get('ps', 10), Yii::$app->request->get('cp', 1), $groupId, $isDeleted, $keyword);
        return $this->success($res);
    }


    /**
     * 删除数据
     * @return array
     */
    public function actionDel()
    {
        $api = new Api(['scenario' => Api::SCENARIO_DEL]);
        $api->attributes = Yii::$app->request->post();
        $res = $api->del();
        if (is_string($res)) {
            return $this->failed($res);
        }

        return $this->success(null);
    }

    /**
     * 接口详情
     * @return array
     */
    public function actionDetail()
    {
        $api = new Api(['scenario' => Api::SCENARIO_DETAIL]);
        $api->attributes = Yii::$app->request->get();
        $res = $api->detail();
        if (is_string($res)) {
            return $this->failed($res);
        }

        return $this->success($res);
    }


    /**
     * 恢复api
     * @return array
     */
    public function actionRestore()
    {
        $id = Yii::$app->request->post('id');
        if (!$id || !is_numeric($id)) {
            return $this->failed('id错误');
        }

        $res = Api::findOne($id);
        if (!$res) {
            return $this->failed('没有找到要恢复的对象');
        }

        $res->is_deleted = Api::IS_DELETED['no'];
        $trans = Yii::$app->db->beginTransaction();
        try {
            if (!$res->save(false)) {
                throw new Exception('恢复失败');
            }
            Group::updateAll(['is_deleted' => Doc::IS_DELETED['no']], ['id' => $res->group_id]);
            $trans->commit();
        } catch (Throwable $t) {
            $trans->rollBack();
            return $this->failed($t->getMessage());
        }

        OperationLog::createLog($res->project_id, $this->userInfo->id, $res->id, OperationLog::ACTION['restore'][0], '接口:' . $res->api_name, OperationLog::OBJECT_TYPE['api'][0]);
        return $this->success();
    }
}
