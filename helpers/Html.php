<?php
namespace app\helpers;

use Yii;
use yii\helpers\BaseHtml;
use yii\helpers\Url;

class Html extends BaseHtml
{
    public static function imgUrl($path, $w = 80, $h = 80)
    {
        return Url::base(true) . Url::to(['/site/glide', 'path' => $path, 'w' => $w, 'h' => $h]);
    }
}