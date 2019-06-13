<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\v1\models\TblQueueService;
use app\modules\v1\models\TblFloor;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\components\AppQuery;
/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblServiceGroup */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tbl-service-group-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'service_group_name')->textInput(['maxlength' => true]) ?>

    <?= Html::activeHiddenInput($model, 'service_group_order') ?>

    <?= $form->field($model, 'floor_id')->widget(Select2::classname(), [
        'data' => AppQuery::getFloorOptions(),
        'options' => ['placeholder' => 'เลือก...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

    <?= $form->field($model, 'queue_service_id')->widget(Select2::classname(), [
        'data' => AppQuery::getQueueServiceOptions(),
        'options' => ['placeholder' => 'เลือก...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
