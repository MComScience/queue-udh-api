<?php

/**
 * Created by PhpStorm.
 * User: Tanakorn Phompak
 * Date: 11/5/2562
 * Time: 12:37
 */
namespace app\assets;

use yii\web\AssetBundle;

class JsBarcodeAsset extends AssetBundle {

    public $sourcePath = '@bower/JsBarcode/dist';

    public $js = [
        'JsBarcode.all.min.js'
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
    ];
}