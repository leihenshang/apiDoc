<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%notification}}".
 *
 * @property int $id
 * @property string $title 标题
 * @property string $content 内容
 * @property int $send_type 0默认1系统
 * @property int $receiver 接收者
 * @property string $create_time
 * @property int $read 0未读1已读
 * @property int $is_deleted
 */
class Notification extends \app\models\BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%notification}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['send_type', 'receiver', 'read', 'is_deleted'], 'integer'],
            [['receiver','content'], 'required'],
            [['create_time'], 'safe'],
            [['title'], 'string', 'max' => 100],
            [['content'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'content' => '内容',
            'send_type' => '0默认1系统',
            'receiver' => '接收者',
            'create_time' => 'Create Time',
            'read' => '0未读1已读',
            'is_deleted' => 'Is Deleted',
        ];
    }

    /**
     * 创建用户通知
     * @param $receiver
     * @param $content
     * @param string $title
     * @param int $sendType
     * @return bool
     */
    public static function createNotice($receiver,$content,$title = '系统消息',$sendType = 1)
    {
        $model = new self();
        $model->content = $content;
        $model->receiver = $receiver;
        $model->title = $title;
        $model->send_type = $sendType;
        return $model->save();
    }
}
