<?php

namespace app\controllers;

use app\behaviors\UserVerify;
use app\models\Api;
use app\models\Bug;
use app\models\BugAssign;
use Yii;
use yii\base\DynamicModel;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\validators\NumberValidator;

/**
 * bug反馈
 * Class BugController
 * @package app\controllers
 */
class BugController extends BaseController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['userVerify'] = [
            'class' => UserVerify::class,
            'actions' => ['*'],  //设置要验证的action,如果留空或者里边放入 * ，则所有的action都要执行验证
            'excludeAction' => [], //要排除的action,在此数组内的action不执行登陆状态验证,
            'projectPermission' => ['update', 'delete', 'create', 'detail']
        ];
        return $behaviors;
    }

    /**
     * 创建数据
     * @return array
     */
    public function actionCreate()
    {
        $bug = new Bug(['scenario' => Bug::SCENARIO_CREATE]);
        $bug->attributes = Yii::$app->request->post();
        $res = $bug->save();
        if ($res) {
            return $this->success();
        }
        return $this->failed(current($bug->getFirstErrors()));
    }

    /**
     * 更新数据
     * @return array
     * @throws
     */
    public function actionUpdate()
    {
        $request = Yii::$app->request->post();
        $id = $request['id'] ?? 0;
        if(!(new  NumberValidator())->validate($id,$err)){
            return $this->failed('id错误');
        }

        $bug = new Bug(['scenario' => Api::SCENARIO_UPDATE]);
        $bug->attributes = $request;
        if (!$bug->validate()) {
            return $this->failed(current($bug->getFirstErrors()));
        }

        [$err, $res] = $bug->updateData($id,$this->userInfo->id);
        if ($err) {
            return $this->failed($err);
        }

        return $this->success($res);
    }

    /**
     * 指派bug
     * @return array
     */
    public function actionAssign()
    {
        $request = Yii::$app->request->post();
        $bugId = $request['bug_id'] ?? 0;
        if(!(new  NumberValidator())->validate($bugId,$err) || $bugId <= 0){
            return $this->failed('bug_id错误');
        }

        $toUserId = $request['to_user_id'] ?? 0;
        if(!(new  NumberValidator())->validate($toUserId,$err)){
            return $this->failed('to_user_id错误');
        }

        $comment = $request['comment'] ?? '';

        $status = $request['status'] ?? 0;
        if(!(new  NumberValidator())->validate($status,$err)){
            return $this->failed('status错误');
        }

        $bugAssign = new BugAssign(['scenario' => BugAssign::SCENARIO_CREATE]);
        $bugAssign->attributes = $request;

        if (!$bugAssign->validate()) {
            return $this->failed(current($bugAssign->getFirstErrors()));
        }

        [$err, $res] = $bugAssign->assign($this->userInfo->id,$bugId,$toUserId,$status,$comment);
        if ($err) {
            return $this->failed($err);
        }

        return $this->success($res);
    }


    /**
     * 列表
     * @return array
     */
    public function actionList()
    {
        $projectId = intval(Yii::$app->request->get('projectId', 0));
        $isDeleted = Yii::$app->request->get('isDeleted', 0);
        $sortType = Yii::$app->request->get('sortType', 'desc');
        $orderBy = Yii::$app->request->get('orderBy', 'create_time');
        $ps = Yii::$app->request->get('ps', 10);
        $cp = Yii::$app->request->get('cp', 1);
        $status = Yii::$app->request->get('status', 0);
        $level = Yii::$app->request->get('level', 0);
        $toUserId = Yii::$app->request->get('toUserId', 0);
        if (!$projectId) {
            return ['code' => 22, 'msg' => '没有projectId'];
        }

        $res = new Bug();
        $res = $res->dataList(
            intval($projectId),
            intval($status),
            intval($level),
            intval($toUserId),
            intval($isDeleted),
            $sortType,
            $orderBy,
            intval($cp),
            intval($ps)
        );
        return $this->success($res);
    }

    public function actionAssignList()
    {
        $sortType = Yii::$app->request->get('sortType', 'ASC');
        $orderBy = Yii::$app->request->get('orderBy', 'create_time');
        $ps = Yii::$app->request->get('ps', 10);
        $cp = Yii::$app->request->get('cp', 1);
        $bugId = Yii::$app->request->get('bugId', 0);
        if (!$bugId) {
            return ['code' => 22, 'msg' => '没有projectId'];
        }

        $res = new BugAssign();
        $res = $res->dataList(
            intval($bugId),
            $sortType,
            $orderBy,
            intval($cp),
            intval($ps)
        );
        return $this->success($res);
    }


    /**
     * 接口详情
     * @return array
     * @throws
     */
    public function actionDetail()
    {
        $id = Yii::$app->request->get('id', 0);
        $validate = DynamicModel::validateData(['id' => $id], [
            [['id'], 'required'],
            [['id'], 'integer', 'integerOnly' => true, 'min' => 1],
        ]);
        if (!$validate->validate()) {
            return $this->failed('id错误');
        }

        $res = (new Bug())->detail($id);
        return $this->success($res);
    }

    /**
     * 删除数据
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->post('id', 0);
        $validate = DynamicModel::validateData(['id' => $id], [
            [['id'], 'required'],
            [['id'], 'integer', 'integerOnly' => true, 'min' => 1],
        ]);
        if (!$validate->validate()) {
            return $this->failed('id错误');
        }

        $res = Bug::findOne($id);
        if(!$res) {
            return $this->failed('没有找到要删除的数据');
        }

        try {
            if (!$res->delete()) {
            throw new Exception('删除失败');
            }
        } catch (StaleObjectException $e) {
        } catch (\Throwable $e) {
            return $this->failed('删除失败');
        }

        return $this->success();
    }

}
