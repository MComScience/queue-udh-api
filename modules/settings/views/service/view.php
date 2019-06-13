<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblService */
?>
<div class="tbl-service-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'service_id',
            'service_code',
            'service_name',
            'service_group_id',
            'service_prefix',
            'service_num_digit',
            'card_id',
            'prefix_id',
            'prefix_running',
            'print_copy_qty',
            'queue_service_id',
            'service_order',
            'service_status',
        ],
    ]) ?>

</div>
