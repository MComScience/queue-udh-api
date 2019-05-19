<?php
namespace app\assets;

use yii\web\AssetBundle;

class ToastrAsset extends AssetBundle 
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'js/toastr/build/toastr.min.css', 
    ];

    public $js = [
        'js/toastr/build/toastr.min.js', 
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}