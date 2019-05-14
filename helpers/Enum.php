<?php

/**
 * Created by PhpStorm.
 * User: Tanakorn Phompak
 * Date: 14/5/2562
 * Time: 10:21
 */
namespace app\helpers;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Inflector;

class Enum extends Inflector
{
    public static function startDateNow()
    {
        return Enum::formatter()->asDate('now', 'php:Y-m-d 00:00:00');
    }

    public static function endDateNow()
    {
        return Enum::formatter()->asDate('now', 'php:Y-m-d 23:59:59');
    }

    public static function formatter()
    {
        return Yii::$app->formatter;
    }
}
