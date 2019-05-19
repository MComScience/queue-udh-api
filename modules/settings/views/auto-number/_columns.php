<?php
use yii\helpers\Url;

return [
    [
        'class' => 'kartik\grid\CheckboxColumn',
        'width' => '20px',
    ],
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
        // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'id',
    // ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'label' => 'ตัวอักษรหน้าเลขคิว',
        'attribute'=>'prefix_id',
        'value' => function($model) {
            return $model->prefix ? $model->prefix->prefix_code : '';
        },
        'hAlign' => 'center'
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'dept_code',
        'hAlign' => 'center'
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'number',
        'hAlign' => 'center'
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'flag',
        'hAlign' => 'center'
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'updated_at',
        'format' => ['date', 'php:Y-m-d'],
        'hAlign' => 'center'
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'vAlign'=>'middle',
        'urlCreator' => function($action, $model, $key, $index) { 
                return Url::to([$action,'id'=>$key]);
        },
        'viewOptions'=>['role'=>'modal-remote','title'=>'View','data-toggle'=>'tooltip'],
        'updateOptions'=>['role'=>'modal-remote','title'=>'Update', 'data-toggle'=>'tooltip'],
        'deleteOptions'=>['role'=>'modal-remote','title'=>'Delete', 
                          'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                          'data-request-method'=>'post',
                          'data-toggle'=>'tooltip',
                          'data-confirm-title'=>'Are you sure?',
                          'data-confirm-message'=>'Are you sure want to delete this item'], 
    ],

];   