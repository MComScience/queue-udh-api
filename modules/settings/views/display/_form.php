<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\modules\v1\models\TblCounter;
use app\widgets\codemirror\CodeMirror;
use app\widgets\codemirror\CodeMirrorAsset;
/* @var $this yii\web\View */
/* @var $model app\modules\v1\models\TblDisplay */
/* @var $form yii\widgets\ActiveForm */
$bundle = CodeMirrorAsset::register($this);
$bundle->css[] = 'theme/paraiso-light.css';
$bundle->js[] = 'mode/css/css.js';
$bundle->js[] = 'addon/selection/active-line.js';
?>

<div class="tbl-display-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'counter_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(TblCounter::find()->asArray()->all(), 'counter_id', 'counter_name'),
        'options' => ['placeholder' => 'เลือก...', 'multiple' => true],
        'pluginOptions' => [
            'allowClear' => true,
            'tags' => true,
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

    <?= $form->field($model, 'service_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map((new \yii\db\Query())
            ->select(['tbl_service.service_id', 'CONCAT(\'(\', tbl_queue_service.queue_service_name ,\') \', tbl_service.service_name) AS service_name'])
            ->from('tbl_service')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->where([
                'tbl_service.service_status' => 1
            ])
            ->all(), 'service_id', 'service_name'),
        'options' => ['placeholder' => 'เลือก...', 'multiple' => true],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'theme' => Select2::THEME_BOOTSTRAP,
    ]) ?>

    <?= $form->field($model, 'display_css')->widget(CodeMirror::className(), [
        'pluginOptions' => [
            'mode' => 'text/css', 
            'theme' => 'paraiso-light',
            'line' => true,
            'lineNumbers' => true,
            'styleActiveLine' => true
        ]
    ]);
    ?>

<?= $form->field($model, 'page_length')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'display_status')->widget(Select2::classname(), [
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
