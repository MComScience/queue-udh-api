<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblKiosk */
?>
<div class="tbl-kiosk-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'kiosk_id',
            'kiosk_name',
            'kiosk_des',
        ],
    ]) ?>

</div>
