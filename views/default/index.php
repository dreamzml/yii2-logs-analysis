<?php

use yii\grid\GridView;
use yii\helpers\Html;

$summeryException = $searchModel->groupStat('exception');
$summeryErrText = $searchModel->groupStat('errText');

$this->registerCss("
    .exception-block{
      color: #8a6d3b;
      background-color: #fcf8e3;
      border-color: #faebcc;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid rgb(181, 149, 60);;
      border-radius: 4px;
      line-height: 40px;
      white-space : nowrap;
    }
    .summer-list-box{
      max-height: 250px;
      overflow-y: scroll;
    }
");

?>

<div class="LogAnalysis-default-index">
    <div class="panel panel-default">
      <div class="panel-heading">Errors Exception Stat</div>
      <div class="panel-body">
          <?php foreach($summeryException as $key=>$item): ?>
            <span class="exception-block"> <?= Html::a("{$key} ({$item})", ['index', 'LogItem[exception]'=>$key],['class'=>'']) ?></span>
          <?php endforeach; ?>
      </div>
    </div>
  <div class="panel panel-default">
    <div class="panel-heading">Errors Summers Stat</div>
    <div class="panel-body summer-list-box">
        <ul class="list-group">
          <?php foreach($summeryErrText as $key=>$item): ?>
            <li class="list-group-item">
              <span class="badge"><?= $item ?></span>
              <?= Html::a( Yii::$app->formatter->asText($key), ['index', 'LogItem[errText]'=>$key],['class'=>'']) ?>
            </li>
          <?php endforeach; ?>
        </ul>
    </div>
  </div>
  
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'log-panel-detailed-grid',
        'options' => ['class' => 'detail-grid-view table-responsive'],
        'filterModel' => $searchModel,
        'rowOptions' => ['logsFile'=>$logsFile],
        //'filterUrl' => $panel->getUrl(),
        //'rowOptions' => function ($model, $key, $index, $grid) {
        //    switch ($model['level']) {
        //        case Logger::LEVEL_ERROR : return ['class' => 'danger'];
        //        case Logger::LEVEL_WARNING : return ['class' => 'warning'];
        //        case Logger::LEVEL_INFO : return ['class' => 'success'];
        //        default: return [];
        //    }
        //},
        'columns' => [
            [
                'attribute' => 'time',
                'filter' => false,
                'value' => function ($data) {
                    return date('m:d H:i:s', $data['time']);
                },
                'headerOptions' => [
                    'class' => 'sort-numerical'
                ]
            ],
            'ip',
            [
                'attribute' => 'exception',
                //'filter' => false,
                'value' => function ($data) {
                    return $data['exception'];
                },
                //'filter' => [
                //    Logger::LEVEL_TRACE => ' Trace ',
                //    Logger::LEVEL_INFO => ' Info ',
                //    Logger::LEVEL_WARNING => ' Warning ',
                //    Logger::LEVEL_ERROR => ' Error ',
                //],
            ],
            [
                'attribute' => 'errText',
                //'filter' => false,
                'value' => function ($data) {
                    return $data['errText'];
                },
                //'filter' => [
                //    Logger::LEVEL_TRACE => ' Trace ',
                //    Logger::LEVEL_INFO => ' Info ',
                //    Logger::LEVEL_WARNING => ' Warning ',
                //    Logger::LEVEL_ERROR => ' Error ',
                //],
            ],
            //'category',
            //[
            //    'attribute' => 'message',
            //    'value' => function ($data) use ($panel) {
            //        $message = Html::encode(is_string($data['message']) ? $data['message'] : VarDumper::export($data['message']));
            //        if (!empty($data['trace'])) {
            //            $message .= Html::ul($data['trace'], [
            //                'class' => 'trace',
            //                'item' => function ($trace) use ($panel) {
            //                    return '<li>' . $panel->getTraceLine($trace) . '</li>';
            //                }
            //            ]);
            //        };
            //        return $message;
            //    },
            //    'format' => 'raw',
            //    'options' => [
            //        'width' => '50%',
            //    ],
            //],
            [
                'attribute' => '查看',
                'format' => 'html',
                'value' => function ($data, $model) {
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['view', 'id'=>$data['key']], ['class'=>'ajax-view']);
                },
                //'filter' => [
                //    Logger::LEVEL_TRACE => ' Trace ',
                //    Logger::LEVEL_INFO => ' Info ',
                //    Logger::LEVEL_WARNING => ' Warning ',
                //    Logger::LEVEL_ERROR => ' Error ',
                //],
            ],
        ],
    ]);
    
    ?>
</div>

<div class="modal fade" id="preview-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <div class="btn-group pull-left">
        </div>
        <strong class="modal-title pull-left">error detail</strong>
        <div class="clearfix"></div>
      </div>
      <div class="modal-body">
        <p>Please wait ...</p>
      </div>
    </div>
  </div>
</div>