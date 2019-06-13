<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblDisplay */
?>
<div class="tbl-display-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'display_id',
            'display_name',
            //'display_css:ntext',
            'counter_id:ntext',
            'service_id:ntext',
            'display_status',
        ],
    ]) ?>

</div>
