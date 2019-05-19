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
        'attribute'=>'counter_service_id',
    ], */
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'counter_service_name',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'counter_service_no',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'counter_service_sound',
        'value' => function($model){
            return $model->serviceSound ? $model->serviceSound->sound_th : '';
        }
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'counter_service_no_sound',
        'value' => function($model){
            return $model->serviceNoSound ? $model->serviceNoSound->sound_th : '';
        }
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'counter_id',
        'value' => function($model){
            return $model->counter ? $model->counter->counter_name : '';
        },
        'group' => true,  // enable grouping,
        'groupedRow' => true,  
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'counter_service_status',
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