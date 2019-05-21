<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\modules\v1\models\User;
use app\modules\v1\models\TblDeptGroup;
/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblKiosk */
/* @var $form yii\widgets\ActiveForm */
?>
<style>
#tblkiosk-departments label {
    display: flex;
}
</style>
<div class="tbl-kiosk-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'kiosk_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'kiosk_des')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'user_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(User::find()->where(['role' => 10])->all(), 'id', 'username'),
        'options' => ['placeholder' => 'Select a state ...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'departments')->checkboxList(ArrayHelper::map(TblDeptGroup::find()->all(), 'dept_group_id', 'dept_group_name'),[

    ]) ?>

  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
