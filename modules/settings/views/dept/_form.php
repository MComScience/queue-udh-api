<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\modules\v1\models\TblDeptGroup;
use app\modules\v1\models\TblCard;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblDept */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tbl-dept-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'dept_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dept_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dept_group_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(TblDeptGroup::find()->asArray()->all(), 'dept_group_id', 'dept_group_name'),
        'options' => ['placeholder' => 'เลือกกลุ่มแผนก...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

    <?= $form->field($model, 'dept_prefix')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dept_num_digit')->textInput() ?>

    <?= $form->field($model, 'card_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(TblCard::find()->asArray()->all(), 'card_id', 'card_name'),
        'options' => ['placeholder' => 'เลือกแบบบัตรคิว...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

    <?= $form->field($model, 'dept_status')->widget(Select2::classname(), [
        'data' => $model->getAllStatus(),
        'options' => ['placeholder' => 'เลือกสถานะ...'],
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
