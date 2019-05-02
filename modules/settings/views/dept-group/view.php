<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblDeptGroup */
?>
<div class="tbl-dept-group-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'dept_group_id',
            'dept_group_name',
        ],
    ]) ?>

</div>
