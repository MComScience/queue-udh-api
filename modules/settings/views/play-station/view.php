<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblPlayStation */
?>
<div class="tbl-play-station-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'play_station_id',
            'play_station_name',
            'counter_id:ntext',
            'counter_service_id:ntext',
            'play_station_status',
        ],
    ]) ?>

</div>
