<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblDept */
?>
<div class="tbl-dept-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'dept_id',
            'dept_name',
            'dept_group_id',
            'dept_prefix',
            'dept_num_digit',
            'card_id',
            'dept_status',
        ],
    ]) ?>

</div>
