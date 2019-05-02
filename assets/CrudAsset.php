<?php

namespace app\assets;

use yii\web\AssetBundle;

class CrudAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/ajaxcrud.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'kartik\grid\GridViewAsset',
    ];

    public function init() {
        // In dev mode use non-minified javascripts
        $this->js = YII_DEBUG ? [
            'js/ModalRemote.js',
            'js/ajaxcrud.js',
        ]:[
            'js/ModalRemote.min.js',
            'js/ajaxcrud.min.js',
        ];

        parent::init();
    }
}