<?php

/* @var $this \yii\web\View */

/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => 'หน้าหลัก', 'url' => ['/site/index']],
            ['label' => 'ผู้ใช้งาน', 'url' => ['/user/admin/index'], 'visible' => !Yii::$app->user->isGuest],
            ['label' => 'จัดการไฟล์', 'url' => ['/file/manager/index'], 'visible' => !Yii::$app->user->isGuest],
            ['label' => 'Runtime', 'url' => ['/file/manager/runtime'], 'visible' => !Yii::$app->user->isGuest],
            [
                'label' => 'ตั้งค่า',
                'items' => [
                    ['label' => 'ตัวอักษรหน้าเลขคิว', 'url' => '/settings/prefix/index'],
                    ['label' => 'ชั้น', 'url' => '/settings/floor/index'],
                    ['label' => 'กลุ่มบริการ', 'url' => '/settings/service-group/index'],
                    // '<li class="divider"></li>',
                    // '<li class="dropdown-header">Dropdown Header</li>',
                    ['label' => 'ชื่อบริการ', 'url' => '/settings/service/index'],
                    ['label' => 'ตู้กดบัตรคิว', 'url' => '/settings/kiosk/index'],
                    ['label' => 'บัตรคิว', 'url' => '/settings/card/index'],
                    ['label' => 'โปรไฟล์เซอร์วิส', 'url' => '/settings/profile-service/index'],
                    //['label' => 'เคาน์เตอร์', 'url' => '/settings/counter/index'],
                    ['label' => 'จุดบริการ/ช่องบริการ', 'url' => '/settings/counter-service/index'],
                    ['label' => 'โปรแกรมเสียงเรียก', 'url' => '/settings/play-station/index'],
                    ['label' => 'จอแสดงผล', 'url' => '/settings/display/index'],
                    ['label' => 'AutoNumber', 'url' => '/settings/auto-number/index'],
                ],
                'visible' => !Yii::$app->user->isGuest
            ],
            //['label' => 'Contact', 'url' => ['/site/contact']],
            Yii::$app->user->isGuest ? (
            ['label' => 'เข้าสู่ระบบ', 'url' => ['/auth/login']]
            ) : (
                '<li>'
                . Html::beginForm(['/auth/logout'], 'post')
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
            )
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left"><?= Yii::$app->name ?> &copy;<?= date('Y') ?></p>

        <p class="pull-right">
            Powered by
            <a href="https://github.com/MComScience" target="_blank"> Tanakorn Phompak</a>
        </p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
