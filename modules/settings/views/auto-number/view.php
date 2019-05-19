<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\AutoNumber */
?>
<div class="auto-number-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'prefix_id',
            'dept_code',
            'number',
            'flag',
            'updated_at',
        ],
    ]) ?>

</div>
