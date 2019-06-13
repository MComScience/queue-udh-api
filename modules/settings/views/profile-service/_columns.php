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
    /* [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'profile_service_id',
    ], */
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'profile_service_name',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'counter_id',
        'value' => function($model){
            return $model->counter ? $model->counter->counter_name : '';
        },
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'service_id',
        'value' => function($model){
            return $model->getServiceList();
        },
        'format' => 'html'
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'examination_id',
        'value' => function($model){
            return $model->getExaminationList();
        },
        'format' => 'html'
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'profile_service_status',
        'value' => function($model){
            return $model->getStatusName();
        },
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