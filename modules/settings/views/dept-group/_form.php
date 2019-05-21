<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\modules\v1\models\TblFloor;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblDeptGroup */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tbl-dept-group-form">

    <?php $form = ActiveForm::begin(); ?>

	<?= $form->field($model, 'floor_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(TblFloor::find()->asArray()->all(), 'floor_id', 'floor_name'),
        'options' => ['placeholder' => 'เลือกชั้น...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

    <?= $form->field($model, 'dept_group_name')->textInput(['maxlength' => true]) ?>

  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
