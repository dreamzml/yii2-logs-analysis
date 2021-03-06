<?php
use yii\bootstrap\NavBar;
use yii\bootstrap\Nav;
use yii\helpers\Html;
use dreamzml\LogAnalysis\models\LogItem;

/* @var $this \yii\web\View */
/* @var $content string */

$asset = dreamzml\LogAnalysis\AppAsset::register($this);

$model      = new LogItem();
if ($model->checkLogTarget()) {
    $logsFile = $model->getLogFiles();
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="none">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<div class="container-fluid page-container">
    <?php $this->beginBody() ?>
    <?php
    NavBar::begin([
                      'brandLabel' => 'Logs Analysis',
                      'brandUrl' => ['default/index'],
                      'options' => ['class' => 'navbar-inverse navbar-fixed-top'],
                  ]);

    if($logsFile){
       $nav = [];
       foreach ($logsFile as $file){
           $nav[] = ['label' => $file, 'url' => ['default/index', 'logsFile'=>$file]];
       }
        
        echo Nav::widget([
                 'options' => ['class' => 'nav navbar-nav'],
                 'items' => $nav,
             ]);
    }
    
    echo Nav::widget([
                         'options' => ['class' => 'nav navbar-nav navbar-right'],
                         'items' => [
                             ['label' => 'Help', 'url' => ['#']],
                             //['label' => 'doc', 'url' => 'http://blog.4568113.com'],
                         ],
                     ]);
    NavBar::end();
    ?>
    <div class="container content-container">
        <?= $content ?>
    </div>
    <div class="footer-fix"></div>
</div>
<footer class="footer">
    <div class="container">
        <p class="pull-left">A Product of <a href="http://www.yiisoft.com/">Yii Software LLC</a></p>
        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
