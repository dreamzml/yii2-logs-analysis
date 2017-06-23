<?php

namespace dreamzml\LogAnalysis\controllers;

use yii\web\Controller;

/**
 * Default controller for the `LogAnalysis` module
 */
class DefaultController extends Controller
{
    public $layout = 'simple';
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
