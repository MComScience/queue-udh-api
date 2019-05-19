<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\modules\v1\models\TblCounter;
use app\modules\v1\models\TblDept;
/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblProfileService */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tbl-profile-service-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'profile_service_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'counter_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(TblCounter::find()->asArray()->all(), 'counter_id', 'counter_name'),
        'options' => ['placeholder' => 'เลือกจุดบริการ...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

    <?= $form->field($model, 'dept_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(TblDept::find()->where(['dept_status' => 1])->asArray()->all(), 'dept_id', 'dept_name'),
        'options' => ['placeholder' => 'เลือก...', 'multiple' => true],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

    <?= $form->field($model, 'profile_service_status')->widget(Select2::classname(), [
        'data' => $model->getStatusLits(),
        'options' => ['placeholder' => 'เลือกสถานะ...'],
        'pluginOptions' => [
            'allowClear' => true,
            'tags' => true,
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
