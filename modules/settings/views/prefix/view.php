<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblPrefix */
?>
<div class="tbl-prefix-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'prefix_id',
            'prefix_code',
        ],
    ]) ?>

</div>
