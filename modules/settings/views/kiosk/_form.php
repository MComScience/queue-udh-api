<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\modules\v1\models\User;
use app\modules\v1\models\TblDeptGroup;
use app\modules\v1\models\TblServiceGroup;
use app\components\AppQuery;
/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblKiosk */
/* @var $form yii\widgets\ActiveForm */
?>
<style>
#tblkiosk-service_groups label {
    display: flex;
}
</style>
<div class="tbl-kiosk-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'kiosk_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'kiosk_des')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'user_id')->widget(Select2::classname(), [
        'data' => AppQuery::getUserKioskOptions(),
        'options' => ['placeholder' => 'Select a state ...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'service_groups')->checkboxList(AppQuery::getServiceGroupOptions(),[]) 
    ?>

  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
