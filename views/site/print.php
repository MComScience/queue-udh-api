<?php

/**
 * Created by PhpStorm.
 * User: Tanakorn Phompak
 * Date: 11/5/2562
 * Time: 12:34
 */

use app\assets\JsBarcodeAsset;
use yii\web\View;

JsBarcodeAsset::register($this);

$this->registerCss(<<<CSS
.row {
  margin: auto !important;
}
.qrcode img {
    margin: auto;
    display: -webkit-inline-box;
}
CSS
);
$this->registerCssFile("@web/css/80mm.min.css", [
    'depends' => [\yii\bootstrap\BootstrapAsset::className()],
]);
//$i = !empty($department['print_copy_qty']) ? $department['print_copy_qty'] : 1;
$this->registerJs(
    "var i = ".$i.";",
    View::POS_HEAD
);
?>
<?php /*
<?php for($x = 0; $x < $i; $x++) { ?>
    <div class="x_content">
        <div
                class="row"
                style="margin-bottom:0px; margin-left:0px; margin-right:0px; margin-top:0px"
        >
            <div
                    class="col-md-12 col-sm-12 col-xs-12"
                    style="padding:0 21px 0px 21px"
            >
                <div class="col-xs-12" style="padding:0">
                    <img
                            alt
                            class="center-block"
                            src="<?= Yii::getAlias('@web/images/udh-logo.png'); ?>"
                            style="width:60px;display: block;margin-right: auto;margin-left: auto;"
                    />
                </div>
            </div>

            <p style="text-align:center;font-size: 13px;color: #5a5a5a;">
        <span style="font-size:14px;">
          <strong>โรงพยาบาลอุดรธานี</strong>
        </span>
            </p>

            <p
                    style="text-align:center;font-size: 13px;color: #5a5a5a;margin-bottom: 1px;"
            >
        <span>
          <strong>
              <?= $department['dept_name'] ?>
          </strong>
        </span>
            </p>

            <p style="text-align:center;font-size: 13px;color: #5a5a5a;margin: 0">
                <span style="font-size:36px">
                  <strong><?= $model['queue_no'] ?></strong>
                </span>
            </p>
            <h4 style="margin-left:1px; margin-right:1px; text-align:center;font-size: 13px;color: #5a5a5a;margin-bottom: 0;margin-top: 0">
            <span style="font-size:18px">
              <strong>HN</strong> :
              <strong>
                <?= $patient['hn'] ?>
              </strong>
            </span>
            </h4>

            <p
                    style="margin-left:1px; margin-right:1px; text-align:center;font-size: 13px;color: #5a5a5a;"
            >
        <span>
          <span style="font-size:16px">
            <strong>ชื่อ</strong> :
            <strong>
              <?= $patient['fullname'] ?>
            </strong>
          </span>
        </span>
            </p>

            <p
                    style="text-align:center;font-size: 13px;color: #5a5a5a;margin-bottom: 1px;font-weight: bold"
            >
        <span>
          สิทธิการรักษา
        </span>
            </p>

            <div class="maininscl_name">
                <p
                        v-if="dataPrint.patient.maininscl_name"
                        style="text-align:left;font-size: 13px;color: #5a5a5a;margin-bottom: 1px;margin-left: 10%;font-weight: bold"
                >
                    <input type="checkbox" checked/>
                    <span style="font-weight: bold">
            <?= $patient['maininscl_name'] ?>
          </span>
                </p>
            </div>

            <div
                    class="col-xs-8 col-xs-offset-2"
                    style="border-top:dashed 1px #404040; padding:4px 0px 3px 0px"
            >
                <div class="col-xs-12" style="padding:1px">
                    <p
                            style="text-align: center;margin-bottom: 0px;font-size: 13px;color: #5a5a5a;"
                    >
            <span style="font-size:14px">
              <strong>ขั้นตอนการรับบริการ</strong>
            </span>
                    </p>
                </div>
            </div>

            <div class="col-xs-12">
                <div
                        class="col-xs-12 text-center"
                        style="padding-top:1px;text-align:center;"
                >
                    <p
                            style="text-align:left;margin-bottom: 0px;font-size: 13px;color: #5a5a5a;"
                    >
            <span style="font-size:13px;font-weight: bold;"
            >1. ชั่งน้ำหนักวัดส่วนสูง</span
            >
                    </p>
                    <p
                            style="text-align:left;margin-bottom: 0px;font-size: 13px;color: #5a5a5a;"
                    >
            <span style="font-size:13px;font-weight: bold;"
            >2. วัดความดันโลหิต</span
            >
                    </p>
                    <p
                            style="text-align:left;margin-bottom: 0px;font-size: 13px;color: #5a5a5a;"
                    >
            <span style="font-size:13px;font-weight: bold;"
            >3. รอซักประวัติ</span
            >
                    </p>
                </div>
            </div>
        </div>

        <div class="row">
            <div
                    class="col-md-12 col-sm-12 col-xs-12"
                    style="padding:0px 21px 0px 21px"
            >
                <div class="col-xs-6" style="padding:0;text-align: right;">
                    <div
                            id="qrcode<?= $i ?>"
                            class="qrcode"
                            style="text-align: right;padding-right: 20px;"
                    >
                    </div>
                </div>

                <div class="col-xs-6" style="padding:0px; text-align:center">
                    <img id="barcode<?= $i ?>"/>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12" style="padding: 10px 0px 0px;">
                <h4
                        class="color"
                        style="margin-top: 0px;margin-bottom: 0px;text-align: center;"
                >
                    <b
                            style="font-family: 'Prompt', sans-serif;font-weight: normal;color: black;"
                    >ขอบคุณที่ใช้บริการ</b
                    >
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-6" style="padding: 10px 0px 0px;">
                <div class="col-xs-12">
                    <p
                            style="text-align: left; margin-bottom: 0px; font-size: 13px; color: rgb(90, 90, 90);"
                    >
                    <span>
                        <?= Yii::$app->formatter->asDate('now', 'php: d M ') .
                        (Yii::$app->formatter->asDate('now', 'php:Y') + 543) ?>
                    </span>
                    </p>
                </div>
            </div>
            <div
                    class="col-md-6 col-sm-6 col-xs-6"
                    style="padding: 10px 0px 0px;text-align: right;"
            >
                <div class="col-xs-12">
                    <p
                            style="text-align: right; margin-bottom: 0px; font-size: 13px; color: rgb(90, 90, 90);"
                    >
                        <span><?= Yii::$app->formatter->asDate('now', 'php: H:i น.') ?></span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <br>
<?php } ?>
*/ ?>
<?= $template ?>
<?php
$this->registerJsFile(
    '@web/js/qrcode.min.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
$this->registerJs(<<<JS
for (x = 0; x < i; x++) {
    /* new QRCode(document.getElementById("qrcode"+x), {
        text: window.location.origin + '/scan-qrcode/'+ {$model->queue_id},
        width: 100,
        height: 100,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    }); */
    if($("#barcode"+x)){
        JsBarcode("#barcode"+x, {$patient->hn}, {
            format: "CODE128A",
            width: 2,
            height:100,
            margin: 0
        });
    }
}
$('p[data-f-id="pbf"]').remove()
$(window).on('load', function() {
    window.print();
    window.onafterprint = function(){
        window.close();
    }
});
JS
);
?>