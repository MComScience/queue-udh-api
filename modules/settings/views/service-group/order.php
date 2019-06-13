<?php
use kartik\form\ActiveForm;
use yii\helpers\Html;
use kartik\sortinput\SortableInput;

$this->title = 'จัดเรียงลำดับกลุ่มบริการ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php
    $form = ActiveForm::begin(['type'=>ActiveForm::TYPE_HORIZONTAL, 'id' => 'form-order']);
?>
<div class="form-group">
    <?= Html::activeLabel($model, 'order_group', ['label'=>'', 'class'=>'col-sm-2 control-label']) ?>
    <div class="col-sm-8">
        <p>
            <span class="label label-warning">ระบบจะเรียงลำดับการแสดงผล "ปุ่มตัวเลือก" หน้าตู้ Kiosk ตามลำดับที่กำหนด</span>
        </p>
        <?= $form->field($model, 'order_group',['showLabels'=>false])->widget(SortableInput::classname(), [
            'items' => $items,
            'hideInput' => false,
            'options' => ['class'=>'form-control', 'readonly'=>true]
        ]); ?>
    </div>
</div>
<div class="form-group" style="margin-bottom:0">
    <div class="col-sm-offset-2 col-sm-10">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>  
    </div>
</div>
<?php ActiveForm::end(); ?>

<?php
$this->registerJs(<<<JS
// Toastr options
toastr.options = {
    "debug": false,
    "newestOnTop": false,
    "positionClass": "toast-top-right",
    "closeButton": true,
    "toastClass": "animated fadeInDown",
};

var \$form = $('#form-order');
\$form.on('beforeSubmit', function() {
    var data = \$form.serialize();
    var \$btn = $('#form-order button[type="submit"]').button('loading')
    $.ajax({
        url: \$form.attr('action'),
        type: \$form.attr('method'),
        data: data,
        dataType: 'json',
        success: function (data) {
            // Implement successful
            socket.emit('UPDATED_SETTINGS', data.data);
            // window.location.reload();
            toastr.success('บันทึกรายการสำเร็จ!.', 'Success', {timeOut: 5000});
            \$btn.button('reset')
        },
        error: function(jqXHR, errMsg) {
            \$btn.button('reset')
            toastr.error(errMsg, 'Error', {timeOut: 5000})
        }
    });
    return false; // prevent default submit
});
JS
);
?>