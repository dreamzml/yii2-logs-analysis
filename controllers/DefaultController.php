<?php

namespace dreamzml\LogAnalysis\controllers;

use yii\web\Controller;
use dreamzml\LogAnalysis\models\LogItem;

use Yii;

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
    public function actionIndex() {
        
        $model      = new LogItem();
        
        //不支持的日志类型，只支持文件类型日志
        if (!$model->checkLogTarget()) {
            return $this->render('not_suport');
        }
        
        
        $logsFiles = $model->getLogFiles();
        
        $logsFile = Yii::$app->request->get('logsFile', Yii::$app->session->get('logsFile', $logsFiles[0]??""));
        Yii::$app->session->set('logsFile', $logsFile);
        
        $params = Yii::$app->request->get();
        $model->initData($logsFile);
        $dataProvider = $model->search($params);
        
        return $this->render('index', [
            'logsFiles'    => $logsFiles,
            'searchModel'  => $model,
            'dataProvider' => $dataProvider,
            'logsFile'     => $logsFile,
        ]);
    }
    
    public function actionView() {
        $id       = Yii::$app->request->get('id');
        $logsFile = Yii::$app->session->get('logsFile');
        
        $model    = new LogItem();
        //不支持的日志类型，只支持文件类型日志
        if (!$model->checkLogTarget()) {
            return $this->render('not_suport');
        }
        
        $model->initData($logsFile);
        $item = $model->findByPk($id);
        return $this->renderPartial('view', [
            'model' => $item,
        ]);
    }
}
