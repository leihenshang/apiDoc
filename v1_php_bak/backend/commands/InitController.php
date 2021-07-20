<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\UserInfo;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Exception;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InitController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'init command')
    {
        echo $message . "\n";

        return ExitCode::OK;
    }

    public function actionUser($resetPwd = false)
    {
        $db = \Yii::$app->getDb();
        //判断是否存在root

        $res = UserInfo::findOne(['name' => 'root']);
        if ($res) {
            if ($resetPwd) {
                $res->pwd = UserInfo::encryptionPwd('123456789');
                if ($res->save(false)) {
                    echo '密码已重置为123456789' . PHP_EOL;
                    return ExitCode::OK;
                } else {
                    echo '重置保存失败' . PHP_EOL;
                    return ExitCode::DATAERR;
                }
            } else {
                echo '用户已存在' . PHP_EOL;
                return ExitCode::DATAERR;
            }

        }
        $sql = /**@lang text */
            <<<SQL
            INSERT INTO user_info
            (name, pwd, email, is_deleted, `type`, state, mobile_number, nick_name )
            VALUES( 'root', :pwd, 'apiDoc@apiDoc.com', 0,3, 1, '77777777777', 'superuser');
SQL;
        try {
            $db->createCommand($sql)->bindValue(':pwd', UserInfo::encryptionPwd('123456789'))->execute();
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            return ExitCode::DATAERR;
        }
        return ExitCode::OK;
    }


}
