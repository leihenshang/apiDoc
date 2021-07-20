<?php

namespace app\models;

/**
 * This is the model class for table "{{%message}}".
 *
 * @property int $id
 * @property int $is_deleted
 * @property string $content 内容
 * @property int $send_type 0默认1手机2邮箱
 * @property string $receive_source 接收方地址
 * @property string $title 标题
 * @property string $expire_time 过期时间
 * @property string $create_time
 * @property string $used_time 使用时间
 * @property int $is_used 0正常1已使用
 * @property string $code 验证码
 */
class Message extends BaseModel
{
    //发送类型
    const SEND_TYPE = [
        'default' => [0, '默认'],
        'phone' => [1, '手机'],
        'email' => [2, '邮箱']
    ];

    //使用状态
    const IS_USED = ['no' => 0, 'yes' => 1];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%message}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_deleted', 'send_type', 'is_used'], 'integer'],
            [['content', 'receive_source', 'expire_time'], 'required'],
            [['expire_time', 'create_time', 'used_time'], 'safe'],
            [['content'], 'string', 'max' => 500],
            [['receive_source', 'title'], 'string', 'max' => 100],
            ['code', 'string', 'max' => 100]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'is_deleted' => 'Is Deleted',
            'content' => '内容',
            'send_type' => '0默认1手机2邮箱',
            'receive_source' => '接收方地址',
            'title' => '标题',
            'expire_time' => '过期时间',
            'create_time' => 'Create Time',
            'used_time' => '使用时间',
            'is_used' => '0正常1已使用',
        ];
    }

    /**
     * 发送邮箱验证码
     * @param string $mail
     * @return array|string
     */
    public static function sendCodeToMail(string $mail = '')
    {
        $number = self::getRandomNum();
        $message = new self();
        $message->receive_source = $mail;
        $message->send_type = self::SEND_TYPE['email'][0];
        $message->expire_time = date('Y-m-d H:i:s', strtotime('+ 3day'));
        $message->content = '验证码:' . $number;
        $message->code = (string)$number;
        if (!$message->save()) {
            return '保存失败,' . current($message->getFirstErrors());
        }
        return $message;
    }

    /**
     * 获取随机数
     * @param int $number 个数
     * @return int
     */
    public static function getRandomNum(int $number = 4)
    {
        return mt_rand(100000, 999999);
    }
}
