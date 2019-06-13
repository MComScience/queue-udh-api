<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\modules\v1\models\TblCounter;
use app\modules\v1\models\TblCounterService;
use yii\helpers\ArrayHelper;
use kartik\widgets\DepDrop;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblPlayStation */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tbl-play-station-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'play_station_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'counter_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(TblCounter::find()->asArray()->all(), 'counter_id', 'counter_name'),
        'options' => ['placeholder' => 'เลือก...', 'multiple' => true],
        'pluginOptions' => [
            'allowClear' => true,
            'tags' => true,
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
        'pluginEvents' => [
            "change" => "function() { addOption($(this).val()) }",
        ]
    ]) ?>

    <?= $form->field($model, 'counter_service_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map((new \yii\db\Query())
            ->select(['tbl_counter_service.counter_service_id', 'CONCAT(\'(\',tbl_counter.counter_name, \') \', tbl_counter_service.counter_service_name) AS counter_service_name'])
            ->from('tbl_counter_service')
            ->innerJoin('tbl_counter', 'tbl_counter.counter_id = tbl_counter_service.counter_id')
            ->where([
                'tbl_counter_service.counter_service_status' => 1,
                'tbl_counter.counter_id' => $model['counter_id']
            ])
            ->all(), 'counter_service_id', 'counter_service_name'),
        'options' => ['placeholder' => 'เลือก...', 'multiple' => true],
        'pluginOptions' => [
            'allowClear' => true,
            'tags' => true,
        ],
        'theme' => Select2::THEME_BOOTSTRAP
    ]) ?>

    <?= $form->field($model, 'play_station_status')->widget(Select2::classname(), [
        'data' => $model->getAllStatus(),
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
$this->registerJs(<<<JS
addOption = function(value) {
    $('#tblplaystation-counter_service_id').empty()
    $.ajax({
        method: "POST",
        url: "/settings/play-station/sub-counter",
        dataType: "json",
        data: {ids: value},
        success: function(obj) {
            $.each( obj.output, function( key, data ) {
                var newOption = new Option(data.text, data.id, false, false);
                $('#tblplaystation-counter_service_id').append(newOption);
            });
            $('#tblplaystation-counter_service_id').trigger('change');
        },
        error: function(jqXHR, errMsg) {
            alert(errMsg);
        }
    });
}
JS
);
?>
