<?php

/**
 * @var $this  yii\web\View
 * @var $model common\models\KeyStorageItem
 */

$this->title = Yii::t('yii', 'Update {modelClass}: ', [
        'modelClass' => 'Key Storage Item',
    ]) . ' ' . $model->key;

$this->params['breadcrumbs'][] = ['label' => Yii::t('yii', 'Key Storage Items'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('yii', 'Update');

?>

<?php echo $this->render('_form', [
    'model' => $model,
]) ?>
