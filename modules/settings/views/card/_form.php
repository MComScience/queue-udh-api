<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblCard */
/* @var $form yii\widgets\ActiveForm */
$this->title = 'ออกแบบบัตรคิว';
?>

<div class="tbl-card-form">

    <?php $form = ActiveForm::begin(['id' => 'form-ticket', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>

    <?= $form->field($model, 'card_name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::activeLabel($model, 'card_template', ['label' => 'ออกแบบ','class'=>'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <?= $form->field($model, 'card_template',['showLabels' => false])->textarea([
                'value' => $model->isNewRecord || empty($model['card_template']) ? $model->defaultCard : $model['card_template']
            ])->hint('<span class="text-danger">หมายเหตุ. ห้าม!!! เปลี่ยนข้อความที่มีเครื่องหมาย {} </span>') ?>
        </div>
        <?= Html::activeLabel($model, 'template', ['label' => 'ตัวอย่างบัตรคิว','class'=>'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
            <div id="editor-preview">
                <?php // $model->isNewRecord || empty($model['card_template']) ? $model->defaultCard : $model['card_template']; ?>
            </div>
        </div>
    </div>

    <?= $form->field($model, 'card_status')->radioList($model->getAllStatus()) ?>

  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="col-sm-4 col-sm-offset-2">
            <?= Html::a('Close', ['/settings/card/index'],['class' => 'btn btn-default']) ?>
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>

<?php
$this->registerJsFile(
    '@web/js/ckeditor/ckeditor.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
$this->registerJsFile(
    '@web/js/moment/moment.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
$this->registerJsFile(
    '@web/js/moment/locale/th.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

$this->registerJs(<<<JS
var d = new Date();
var y = d.getFullYear() + 543;
var editor  = CKEDITOR.inline( 'tblcard-card_template',{
    contenteditable: true,
    language: 'th',
    extraPlugins: 'sourcedialog',
    uiColor: '#f1f3f6'
});
editor.on('change',function(){
    previewCard();
});

var previewCard = function() {
    var data = editor.getData()
    .replace('{hn}','123456')
    .replace('{number}','A001')
    .replace('{dept_name}','อายุรกรรมทั่วไป')
    .replace('{message_right}','ชำระเงินเอง (เงินสด)')
    .replace('{fullname}','นาย บุญเพ็ง เพ็งบุญ')
    .replace('{date}',moment().format("D MMM ") + (y.toString()).substr(2))
    .replace('{time}', moment().format("HH:mm") + ' น.');
    $('#editor-preview').html(data);
    editor.updateElement();
}

previewCard();
JS
);
?>