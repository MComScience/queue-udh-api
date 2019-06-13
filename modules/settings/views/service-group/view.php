<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblServiceGroup */
?>
<div class="tbl-service-group-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'service_group_id',
            'service_group_name',
            'service_group_order',
            'floor_id',
            'queue_service_id',
        ],
    ]) ?>

</div>
