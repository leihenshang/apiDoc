<?php

namespace app\controllers;

use app\behaviors\UserVerify;
use app\models\Doc;
use app\models\Group;
use Throwable;
use Yii;
use yii\base\DynamicModel;
use yii\base\Exception;

class DocController extends BaseController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['userVerify'] = [
            'class' => UserVerify::class,
            'actions' => ['*'],  //设置要验证的action,如果留空或者里边放入 * ，则所有的action都要执行验证
            'excludeAction' => [],
            'projectPermission' => ['create','update','delete','restore'], //要排除的action,在此数组内的action不执行登陆状态验证
        ];
        return $behaviors;
    }

    /**
     * 创建数据
     * @return array
     */
    public function actionCreate()
    {
        $doc = new Doc(['scenario' => Doc::SCENARIO_CREATE]);
        $doc->attributes = Yii::$app->request->post();
        if (!$doc->validate()) {
            return $this->failed(current($doc->getFirstErrors()));
        }
        $doc->user_id = $this->userInfo->id;
        $res = $doc->createData();
        if (is_string($res)) {
            return $this->failed($res);
        }

        return $this->success();
    }

    /**
     * 更新数据
     * @return array
     */
    public function actionUpdate()
    {
        $doc = new Doc(['scenario' => Doc::SCENARIO_UPDATE]);
        $request = Yii::$app->request->post();
        $doc->attributes = $request;
        if (!$doc->validate()) {
            return $this->failed(current($doc->getFirstErrors()));
        }
        if (!$doc->id) {
            return $this->failed('id不能为空');
        }

        $res = $doc->updateData($request);
        if (is_string($res)) {
            return $this->failed($res);
        }

        return ['data' => '成功'];
    }

    /**
     * api列表
     * @return array
     */
    public function actionList()
    {
        $params = Yii::$app->request->get();
        $res = new Doc();
        $projectId  =  $params['projectId'] ?? null;
        $groupId  =  $params['groupId'] ?? 0;
        $validate = DynamicModel::validateData(compact(['projectId','groupId']),[
            [['projectId','groupId'],'required'],
            [['projectId','groupId'],'number'],
        ] );
        if(!$validate->validate()) {
            return $this->failed(current($validate->getFirstErrors()));
        }


        $res = $res->dataList(
            $projectId,
            $groupId,
            Yii::$app->request->get('ps', 10),
            Yii::$app->request->get('cp', 1),
            Yii::$app->request->get('isDeleted'),
            Yii::$app->request->get('keyword'),
            Yii::$app->request->get('isTop')
        );
        if (is_string($res)) {
            return $this->success([], $res);
        }
        return $this->success($res);
    }


    /**
     * 删除数据
     * @return array
     */
    public function actionDelete()
    {
        $doc = new Doc(['scenario' => Doc::SCENARIO_DELETE]);
        $doc->attributes = Yii::$app->request->post();
        if (!$doc->validate()) {
            return $this->failed(current($doc->getFirstErrors()));
        }

        $res = $doc->deleteData();
        if (is_string($res)) {
            return $this->failed($res);
        }

        return $this->success();
    }

    /**
     * 详情
     * @return array
     */
    public function actionDetail()
    {
        $doc = new Doc(['scenario' => Doc::SCENARIO_DETAIL]);
        $doc->attributes = Yii::$app->request->get();
        $res = $doc->detail();
        if (is_string($res)) {
            return $this->failed($res);
        }

        return $this->success($res);
    }

    /**
     * 恢复doc
     * @return array
     */
    public function actionRestore()
    {
        $id = Yii::$app->request->post('id');
        if (!$id || !is_numeric($id)) {
            return $this->failed('id错误');
        }

        $res = Doc::findOne($id);
        if (!$res) {
            return $this->failed('没有找到要恢复的对象');
        }

        $res->is_deleted = Doc::IS_DELETED['no'];
        $trans = Yii::$app->db->beginTransaction();
        try {
            if (!$res->save()) {
                throw new Exception('恢复失败');
            }
            Group::updateAll(['is_deleted' => Doc::IS_DELETED['no']], ['id' => $res->group_id]);
            $trans->commit();
        } catch (Throwable $t) {
            $trans->rollBack();
            return $this->failed($t->getMessage());
        }

        return $this->success();
    }

}