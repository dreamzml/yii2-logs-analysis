<?php

namespace dreamzml\LogAnalysis;

use Yii;
/**
 * LogAnalysis module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'dreamzml\LogAnalysis\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    
        //add bootstrop alias
        $packDir = dirname(__DIR__);
        Yii::setAlias('@dreamzml', $packDir);

        // custom initialization code goes here
    }
}
