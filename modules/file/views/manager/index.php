<?php

/**
 * Created by PhpStorm.
 * User: Tanakorn Phompak
 * Date: 13/5/2562
 * Time: 13:58
 */
$this->title = Yii::t('app', 'File Manager');

$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
    <div class="col-xs-12">
        <?php echo alexantr\elfinder\ElFinder::widget([
            'connectorRoute' => ['connector'],
            'settings' => [
                'height' => '500px',
                'width' => '100%'
            ],
            'buttonNoConflict' => true,
        ]) ?>
    </div>
</div>
