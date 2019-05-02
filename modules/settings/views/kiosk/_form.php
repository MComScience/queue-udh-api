<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblKiosk */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tbl-kiosk-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'kiosk_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'kiosk_des')->textInput(['maxlength' => true]) ?>

  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
