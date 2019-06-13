<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\modules\v1\models\TblCounter;
use app\modules\v1\models\TblService;
use app\modules\v1\models\TblQueueService;
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

    <?= $form->field($model, 'queue_service_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(TblQueueService::find()->asArray()->all(), 'queue_service_id', 'queue_service_name'),
        'options' => ['placeholder' => 'เลือก...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
        'pluginEvents' => [
            "change" => "function() { addOption($(this).val()) }",
        ]
    ]) ?>

    <?= $form->field($model, 'service_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map((new \yii\db\Query())
        ->select(['tbl_service.service_id', 'CONCAT(\'(\', tbl_queue_service.queue_service_name ,\') \', tbl_service.service_name) AS service_name'])
        ->from('tbl_service')
        ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
        ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
        ->where([
            'tbl_service.service_status' => 1,
            'tbl_service_group.queue_service_id' => $model['queue_service_id']
        ])
        ->all(), 'service_id', 'service_name'),
        'options' => ['placeholder' => 'เลือก...', 'multiple' => true],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

    <?= $form->field($model, 'examination_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map((new \yii\db\Query())
        ->select(['tbl_service.service_id', 'CONCAT(\'(\', tbl_queue_service.queue_service_name ,\') \', tbl_service.service_name) AS service_name'])
        ->from('tbl_service')
        ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
        ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
        ->where([
            'tbl_service.service_status' => 1,
            'tbl_service_group.queue_service_id' => 2
        ])
        ->all(), 'service_id', 'service_name'),
        'options' => ['placeholder' => 'เลือก...', 'multiple' => true],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ])->hint('กำหนดเฉพาะจุดซักประวัติ') ?>

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

<?php
$queue_service_id = !$model->isNewRecord ? $model['queue_service_id'] : 1;
$this->registerJs(<<<JS
if({$queue_service_id} === 2) {
    $('.field-tblprofileservice-examination_id').hide();
}
addOption = function(value) {
    if(value == '1') {
        $('.field-tblprofileservice-examination_id').show();
    } else {
        $('.field-tblprofileservice-examination_id').hide();
    }
    $('#tblprofileservice-service_id').empty()
    $.ajax({
        method: "GET",
        url: "/settings/profile-service/sub-service",
        dataType: "json",
        data: {id: value},
        success: function(obj) {
            $.each( obj.output, function( key, data ) {
                var newOption = new Option(data.text, data.id, false, false);
                $('#tblprofileservice-service_id').append(newOption);
            });
            $('#tblprofileservice-service_id').trigger('change');
        },
        error: function(jqXHR, errMsg) {
            alert(errMsg);
        }
    });
}
JS
);
?>
