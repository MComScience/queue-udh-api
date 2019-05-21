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
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'dept_id',
        'hAlign' => 'center'
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'dept_group_id',
        'group' => true,  // enable grouping,
        'groupedRow' => true,  
        'value' => function ($model) {
            return $model->deptGroup ? $model->deptGroup->dept_group_name : '';
        },
    ],
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'dept_name',
    ],
    /* [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'dept_prefix',
    ], */
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'dept_num_digit',
        'hAlign' => 'center'
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'card_id',
        'value' => function ($model) {
            return $model->card ? $model->card->card_name : '';
        },
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'prefix_id',
        'value' => function ($model) {
            return $model->prefix ? $model->prefix->prefix_code : '';
        },
        'hAlign' => 'center'
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'prefix_running',
        'header' => 'is running',
        'hAlign' => 'center'
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'print_copy_qty',
        'hAlign' => 'center'
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'dept_order',
        'label' => 'ลำดับ',
        'hAlign' => 'center'
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'dept_status',
        'value' => function ($model) {
            return $model->statusName;
        },
        'hAlign' => 'center',
        'noWrap' => true
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'vAlign' => 'middle',
        'urlCreator' => function ($action, $model, $key, $index) {
            return Url::to([$action, 'id' => $key]);
        },
        'viewOptions' => ['role' => 'modal-remote', 'title' => 'View', 'data-toggle' => 'tooltip'],
        'updateOptions' => ['role' => 'modal-remote', 'title' => 'Update', 'data-toggle' => 'tooltip'],
        'deleteOptions' => ['role' => 'modal-remote', 'title' => 'Delete',
            'data-confirm' => false, 'data-method' => false,// for overide yii data api
            'data-request-method' => 'post',
            'data-toggle' => 'tooltip',
            'data-confirm-title' => 'Are you sure?',
            'data-confirm-message' => 'Are you sure want to delete this item'],
    ],

];   