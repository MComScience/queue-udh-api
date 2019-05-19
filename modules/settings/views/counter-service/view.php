<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblCounterService */
?>
<div class="tbl-counter-service-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'counter_service_id',
            'counter_service_name',
            'counter_service_no',
            'counter_service_sound',
            'counter_service_no_sound',
            'counter_id',
            'counter_service_status',
        ],
    ]) ?>

</div>
