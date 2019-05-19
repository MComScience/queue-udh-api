<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblCounter */
?>
<div class="tbl-counter-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'counter_id',
            'counter_name',
        ],
    ]) ?>

</div>
