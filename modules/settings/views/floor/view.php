<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblFloor */
?>
<div class="tbl-floor-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'floor_id',
            'floor_name',
        ],
    ]) ?>

</div>
