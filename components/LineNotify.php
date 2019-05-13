<?php

/**
 * Created by PhpStorm.
 * User: Tanakorn Phompak
 * Date: 13/5/2562
 * Time: 15:06
 */

namespace app\components;

use yii\base\Component;
use yii\helpers\Json;
use yii\httpclient\Client;

class LineNotify extends Component
{
    const TOKEN = 'LrkwgjLzvcPYtnnXb5lt8RSiu7cQtrltsEHIzZglUDo';

    const BASE_URL = 'https://notify-api.line.me/api/notify';

    public function sendMessage($message)
    {
        if (strlen($message) < 1000) {
            $queryData = ['message' => $message];
            $queryData = http_build_query($queryData, '', '&');

            $queryData = array('message' => $message);
            $queryData = http_build_query($queryData, '', '&');
            $headerOptions = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
                        . "Authorization: Bearer " . self::TOKEN . "\r\n"
                        . "Content-Length: " . strlen($queryData) . "\r\n",
                    'content' => $queryData
                )
            );
            $context = stream_context_create($headerOptions);
            $result = file_get_contents(self::BASE_URL, FALSE, $context);
            $res = json_decode($result);
            return $res;
        }
    }
}
