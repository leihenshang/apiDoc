<?php

namespace app\controllers;

use app\models\Property;
use Yii;
use yii\helpers\ArrayHelper;

class PropertyController extends BaseController
{
    /**
     * 获取属性列表
     * @return array
     */
    public function actionList()
    {
        $type = Yii::$app->request->get('tag', null);
        $res = Property::find()->select('tag,tag_name,description,group');
        if ($type) {
            $res = $res->where(['tag' => $type])->all();
        } else {
            $res = $res->all();
        }
        if ($res) {
            $res = ArrayHelper::index($res, null, 'tag');
        }

        $varType = $res['var_type'];
        $group = array_column($varType, null, 'group');
        $groupKey = array_unique(array_keys($group));
        $data = [];
        foreach ($groupKey as $value) {
            foreach ($varType as $item) {
                if ($item['group'] === $value) {
                    $data[$value][] = $item;
                }
            }
        }

        $res['var_type'] = $data;
        return $this->success($res);
    }

}
