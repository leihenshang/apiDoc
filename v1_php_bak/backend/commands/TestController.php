<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Elasticsearch\ClientBuilder;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class TestController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        return ExitCode::OK;
    }

    public function actionSet()
    {
        $params = array(
            '127.0.0.1:9200'
        );
        $client = ClientBuilder::create()->setHosts($params)->build();

        $params = [
            'index' => 'my_index1',
            'type' => 'my_type1',
            'id' => 'my_id1',
            'body' => ['testField' => 'abc']
        ];

        $response = $client->index($params);
        print_r($response);
    }

    public function actionGet()
    {
        $client = $this->getClient();
        $params = [
            'index' => 'my_index1',
            'type' => 'my_type1',
            'id' => 'my_id1'
        ];

        $response = $client->get($params);
        print_r($response);
    }

    private function getClient()
    {
        $params = array(
            '127.0.0.1:9200'
        );
        $client = ClientBuilder::create()->setHosts($params)->build();
        return $client;
    }

    public function actionSearch($arg='')
    {
        $client = $this->getClient();
        $params = [
            'index' => 'my_index1',
            'type' => 'my_type1',
            'body' => [
                'query' => [
                    'match' => [
                        'testField' => !$arg ? 'abc' : $arg
                    ]
                ]
            ]
        ];

        $response = $client->search($params);
        print_r($response);
    }

    public function actionDel()
    {
        $client = $this->getClient();
        $params = [
            'index' => 'my_index1',
            'type' => 'my_type1',
            'id' => 'my_id1',
        ];

        $response = $client->delete($params);
        print_r($response);
    }
}
