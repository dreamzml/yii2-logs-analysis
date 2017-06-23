<?php

namespace dreamzml\LogAnalysis;

use yii\web\AssetBundle;

/**
 * ogAnalysis module asset
 */
class AppAsset extends AssetBundle
{
    public $sourcePath = '@dreamzml/yii2-logs-analysis/assets';
    public $css = [
        'main.css',
    ];
    public $js = [
        //'gii.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'yii\gii\TypeAheadAsset',
    ];
}
