<?php

/**
 * Created by PhpStorm.
 * User: Tanakorn Phompak
 * Date: 14/5/2562
 * Time: 11:20
 */
use yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

<?= $form->field($model, 'excelFile')->fileInput() ?>

<?= \yii\helpers\Html::submitButton('Submit', ['class' => 'btn btn-info']) ?>

<?php ActiveForm::end() ?>
