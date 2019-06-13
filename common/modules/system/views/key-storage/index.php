<?php

use yii\grid\GridView;
use yii\helpers\Html;
/**
 * @var $this         yii\web\View
 * @var $searchModel  backend\modules\system\models\search\KeyStorageItemSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model        common\models\KeyStorageItem
 */

$this->title = Yii::t('yii', 'Key Storage Items');

$this->params['breadcrumbs'][] = $this->title;

?>
<p>
    <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
        <i class="glyphicon glyphicon-plus"></i> เพิ่มรายการ
    </button>
</p>
<div class="collapse" id="collapseExample">
  <div class="well">
    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>
  </div>
</div>
<?php echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'options' => [
        'class' => 'grid-view table-responsive',
    ],
    'columns' => [
        [
            'class' => '\yii\grid\SerialColumn'
        ],

        'key',
        'value',

        [
            'class' => '\yii\grid\ActionColumn',
            'template' => '{update} {delete}'
        ],
    ],
]); ?>
