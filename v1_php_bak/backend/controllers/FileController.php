<?php

namespace app\controllers;

use app\behaviors\UserVerify;
use Yii;
use yii\base\DynamicModel;
use yii\web\UploadedFile;

class FileController extends BaseController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['userVerify'] = [
            'class' => UserVerify::class,
            'actions' => ['upload-img'],  //设置要验证的action,如果留空或者里边放入 * ，则所有的action都要执行验证
            'excludeAction' => [], //要排除的action,在此数组内的action不执行登陆状态验证
        ];
        return $behaviors;
    }

    /**
     * 上传图片
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUploadImg()
    {
        if (!Yii::$app->request->isPost) {
            return $this->failed('必须使用POST方法');
        }

        $file = UploadedFile::getInstanceByName('img');
        $model = DynamicModel::validateData(['img' => $file],
            [[
                ['img'],
                'file',
                'skipOnEmpty' => false,
                'extensions' => implode(',', $this->allowImageFile()),
                'maxSize' => 1024 * 1024 * 1,
                'mimeTypes' => 'image/*'

            ]]
        );

        if (!$model->validate()) {
            return $this->failed(current($model->getFirstErrors()));
        }

        $relativePath = 'uploads' . DIRECTORY_SEPARATOR . date("Ymd", time());
        $savePath = Yii::getAlias('@app/web') . DIRECTORY_SEPARATOR . $relativePath;
        if (!is_dir($savePath)) {
            if (!mkdir($savePath,0777,true)) {
                return $this->failed('创建文件夹失败');
            }
        }

        $saveName = md5(time().$file->baseName.mt_rand(1000,1000000));
        $fullPath = $relativePath. DIRECTORY_SEPARATOR. $saveName . '.' . $file->extension;
        //保存数据
        $file->saveAs($fullPath);
        $fullPath = str_replace('\\', '/',$fullPath );
        return $this->success(['url' => $fullPath]);
    }

    /**
     * 返回允许的图片类型
     * @return string[]
     */
    private function allowImageFile()
    {
        return [
            'png',
            'PNG',
            'jpg',
            'JPG',
            'gif',
            'GIF',
        ];
    }
}