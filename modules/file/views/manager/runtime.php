<?php
$this->title = Yii::t('app', 'File Manager');

$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
    <div class="col-xs-12">
        <?php echo alexantr\elfinder\ElFinder::widget([
            'connectorRoute' => ['/file/manager/connector2'],
            'settings' => [
                'height' => '500px',
                'width' => '100%'
            ],
            'buttonNoConflict' => true,
        ]) ?>
    </div>
</div>
