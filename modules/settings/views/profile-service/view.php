<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblProfileService */
?>
<div class="tbl-profile-service-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'profile_service_id',
            'profile_service_name',
            'counter_id',
            'dept_id:ntext',
            'profile_service_status',
        ],
    ]) ?>

</div>
