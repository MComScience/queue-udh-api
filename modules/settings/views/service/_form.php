<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\modules\v1\models\TblPrefix;
use app\modules\v1\models\TblCard;
use app\modules\v1\models\TblQueueService;
use app\components\AppQuery;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblService */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tbl-service-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'service_code')->textInput() ?>

    <?= $form->field($model, 'service_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'service_group_id')->widget(Select2::classname(), [
        'data' => AppQuery::getServiceGroupOptions(),
        'options' => ['placeholder' => 'เลือกกลุ่มแผนก...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

    <?= Html::activeHiddenInput($model, 'service_prefix') ?>

    <?= $form->field($model, 'prefix_id')->widget(Select2::classname(), [
        'data' => AppQuery::getPrefixOptions(),
        'options' => ['placeholder' => 'เลือก...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

    <?= $form->field($model, 'prefix_running')->radioList($model->runningOptions()) ?>

    <?= $form->field($model, 'service_num_digit')->textInput() ?>

    <?= $form->field($model, 'card_id')->widget(Select2::classname(), [
        'data' => AppQuery::getCardOptions(),
        'options' => ['placeholder' => 'เลือกแบบบัตรคิว...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

    <?= $form->field($model, 'print_copy_qty')->textInput() ?>

    <?= $form->field($model, 'service_status')->widget(Select2::classname(), [
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
