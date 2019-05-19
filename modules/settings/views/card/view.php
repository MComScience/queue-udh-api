<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblCard */
?>
<div class="tbl-card-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'card_id',
            'card_name',
            'card_template:ntext',
            'card_status',
        ],
    ]) ?>

</div>
