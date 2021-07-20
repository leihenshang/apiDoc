<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%property}}".
 *
 * @property int $id
 * @property string $tag 标记名
 * @property string $tag_name 标记名
 * @property string $description 描述
 */
class Property extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%property}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tag', 'tag_name'], 'required'],
            [['tag', 'tag_name', 'description'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tag' => '标记名',
            'tag_name' => '标记名',
            'description' => '描述',
        ];
    }
}
