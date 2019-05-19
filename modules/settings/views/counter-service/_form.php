<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\modules\v1\models\TblCounter;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblCounterService */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tbl-counter-service-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'counter_service_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'counter_service_no')->textInput() ?>

    <?= $form->field($model, 'counter_service_sound')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(
            (new \yii\db\Query())
                ->select(['CONCAT(tbl_sound.sound_name,\' \',\'(\',tbl_sound.sound_th,\')\') AS sound_name', 'tbl_sound.sound_id'])
                ->from('tbl_sound')
                ->where('sound_name LIKE :query')
                ->addParams([':query'=>'%Service%'])
                ->all(),'sound_id','sound_name'),
        'options' => ['placeholder' => 'เลือกรายการ...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

    <?= $form->field($model, 'counter_service_no_sound')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(
                (new \yii\db\Query())
                ->select(['CONCAT(tbl_sound.sound_name,\' \',\'(\',tbl_sound.sound_th,\')\') AS sound_name', 'tbl_sound.sound_id'])
                ->from('tbl_sound')
                ->where('sound_name NOT LIKE :query')
                ->addParams([':query'=>'%Service%'])
                ->all(),'sound_id','sound_name'),
        'options' => ['placeholder' => 'เลือกรายการ...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

    <?= $form->field($model, 'counter_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(TblCounter::find()->asArray()->all(), 'counter_id', 'counter_name'),
        'options' => ['placeholder' => 'เลือกรายการ...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

    <?= $form->field($model, 'counter_service_status')->widget(Select2::classname(), [
        'data' => $model->getStatusLits(),
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
