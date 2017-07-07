<?php
/**
 * Created by PhpStorm.
 * User: 16020028
 * Date: 2017/6/27
 * Time: 19:49
 */

namespace dreamzml\LogAnalysis\models;

use Yii;
use yii\log\FileTarget;
use yii\data\ArrayDataProvider;
use yii\debug\components\search\Filter;
use yii\debug\models\search\Base;

/**
 * Search model for requests manifest data.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since  2.0
 */
class LogItem extends Base
{
    public $logTarget;
    public $logPath;
    public $logFile;
    public $models;
    /**
     * @var string tag attribute input search value
     */
    public $exception;
    /**
     * @var string ip attribute input search value
     */
    public $ip;
    /**
     * @var string method attribute input search value
     */
    public $errText;
    /**
     * @var string url attribute input search value
     */
    public $time;
    /**
     * @var string status code attribute input search value
     */
    public $stackTrace;
    /**
     * @var integer sql count attribute input search value
     */
    public $cookie;
    /**
     * @var integer total mail count attribute input search value
     */
    public $server;
    /**
     * @var array critical codes, used to determine grid row options.
     */
    public $session;
    
    
    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['exception', 'ip', 'errText', 'time', 'stackTrace', 'cookie', 'server', 'session'], 'safe'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'exception'  => '错误类型',
            'ip'         => '请请IP',
            'errText'    => '错误迅息',
            'time'       => '时间',
            'stackTrace' => '堆栈跟踪',
            'cookie'     => '$_COOKIE',
            'server'     => '$_SERVER',
            'session'    => '$_SESSION',
        ];
    }
    
    /**
     * Returns data provider with filled models. Filter applied if needed.
     *
     * @param array $params an array of parameter values indexed by parameter names
     * @param array $models data to return provider for
     *
     * @return \yii\data\ArrayDataProvider
     */
    public function search($params) {
        $this->models = $this->getModels();
        $dataProvider = new ArrayDataProvider([
                                                  'allModels'  => $this->models,
                                                  'sort'       => [
                                                      'attributes' => [
                                                          'time' => [
                                                              'default' => SORT_DESC,
                                                          ],
                                                      ],
                                                      //'params'=>['sort'=>'-time'],
                                                  ],
                                                  'pagination' => [
                                                      'pageSize' => 50,
                                                  ],
                                              ]);
        // $dataProvider->setSort(['attributes'=>['time'=>SORT_DESC]]);
        
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        
        $filter = new Filter();
        $this->addCondition($filter, 'exception', true);
        $this->addCondition($filter, 'ip', true);
        $this->addCondition($filter, 'errText', true);
        $this->addCondition($filter, 'stackTrace');
        $this->addCondition($filter, 'cookie');
        $this->addCondition($filter, 'server');
        $this->addCondition($filter, 'session');
        $dataProvider->allModels = $filter->filter($this->models);
        
        return $dataProvider;
    }
    
    /**
     * 校验日志类型是否支持，
     * @return bool判断日志类型是否支持，不支持返回false
     */
    public function checkLogTarget() {
        $log             = Yii::$app->log;
        $this->logTarget = $log->targets[0];
        //不支持的日志类型，只支持文件类型日志
        $suport = ($this->logTarget instanceof FileTarget);
        if ($suport) {
            $this->logPath = dirname($this->logTarget->logFile);
        }
        
        return $suport;
    }
    
    /**
     * 获取日志目录下的所有日志文件列表
     * @return array
     */
    public function getLogFiles() : array {
        $dirFiles = scandir($this->logPath);
        $logFiles = [];
        foreach ($dirFiles as $f) {
            if (strpos($f, '.log') !== false && (strpos($f, '.swp') === false) && !is_dir($this->logPath.'/'.$f)) {
                $logFiles[] = $f;
            }
        }
        
        return $logFiles;
    }
    
    /**
     * @param string $file
     */
    public function initData(string $file)
    {
        $logFile = $this->logPath.'/'.$file;
    
        if (!file_exists($logFile)) {
            return false;
        }
        $this->logFile = $file;
        $this->getModels($file);
        return true;
    }
    
    /**
     * 获取数据
     * @param string $file
     *
     * @return array
     */
    public function getModels(): array
    {
        if(!empty($this->models)){
            return $this->models;
        }
        $this->models = $this->getDataBylogfile($this->logFile);
        return $this->models;
    }
    
    /**
     * 从日志文件获取日志数据
     * @param string $file
     *
     * @return array
     */
    public function getDataBylogfile(string $file) : array {
        if(empty($file)){
            return [];
        }
        
        $logFile = $this->logPath.'/'.$file;
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $f         = fopen($logFile, "r");
        $errorBock = [];
        $block     = null;
        $line1     = null;
        $line2     = null;
        $line3     = null;
        while (!feof($f)) {
            $line = trim(fgets($f));
            
            if (($line == 'Stack trace:' || ($this->isInStack($line) && !$this->isInStack($line1) )) && ($line2 === ']')) {
                $errorBock[] = trim($block.PHP_EOL.$line3.PHP_EOL.$line2);
                $block = $line1;
                $line1 = null;
                $line2 = null;
                $line3 = null;
            }elseif(($line == 'Stack trace:' || ($this->isInStack($line) && !$this->isInStack($line1) && !$this->isInStack($line2))) && $line3 === ']'){
                $errorBock[] = trim($block.PHP_EOL.$line3);
                $block = $line2.PHP_EOL.$line1;
                $line1 = null;
                $line2 = null;
                $line3 = null;
            }elseif(!$this->isErrorSummeryLine($line) && $this->isErrorSummeryLine($line1)){
                if($this->isErrorSummeryLine($line2) && !$this->isErrorSummeryLine($line3)){
                    $errorBock[] = trim($block.PHP_EOL.$line3);
                    $block = $line2.PHP_EOL.$line1;
                }elseif(!$this->isErrorSummeryLine($line2)){
                    $errorBock[] = trim($block.PHP_EOL.$line3.PHP_EOL.$line2);
                    $block = $line1;
                }else{
                    $errorBock[] = trim($block);
                    $block = $line3.PHP_EOL.$line2.PHP_EOL.$line1;
                }
                $line1 = null;
                $line2 = null;
                $line3 = null;
            }elseif(($this->isNoVarName($line) && $line1 === ']')){
                $errorBock[] = trim($block.PHP_EOL.$line3.PHP_EOL.$line2.PHP_EOL.$line1);
                $block = '';
                $line1     = null;
                $line2     = null;
                $line3     = null;
            } else {
                $block .= PHP_EOL.$line3;
            }
            $line3 = $line2;
            $line2 = $line1;
            $line1 = $line;
        }
        fclose($f);
        
        //$itemData = $this->decodeBlockData($errorBock[2]);
        //print_r($itemData);exit;
        
        $errorDataList = [];
        foreach ($errorBock as $k => $item) {
            if(empty($item)){
                continue;
            }
            
            $itemData        = $this->decodeBlockData($item, $k);
            $errorDataList[] = $itemData;
            //if ($k > 1)
            //    break;
        }
        
        return $errorDataList;
    }
    
    /**
     * 分析日志数据区块
     * @param string $str
     *
     * @return array
     */
    public function decodeBlockData(string $str) : array {
        //var_dump($str);exit;
        $itemVars = [];
        
        $itemVars['text'] = $str;
        $itemVars['key']  = md5($str);
        $str              = str_replace(['\'$_', ': $_'], ['\'$v1_', ': $v2_'], $str);
        $vars             = explode('$_', $str);

        $vars[0]          = str_replace(['\'$v1_', ': $v2_'], ['\'$_', ': $_'], $vars[0]);

        //错误描述
        $errorSummer      = $vars[0];
        $errorSummer      = explode("\n", $errorSummer);
        $errorSummerTitle = array_shift($errorSummer);
        if($this->isNoVarName($errorSummerTitle)){
            if($this->isInfoDespLine(end($errorSummer))){
                array_pop($errorSummer);
            }
            $varStr = join($errorSummer, "\n");
            if(strpos($varStr, 'in /') !== false){
                list($varStr) = explode('in /', $varStr);
            }

            eval("\$data = [$varStr;");
            preg_match('/(.*?) (.*?) \[(.*)\](.*?)\[(error|warning|info)\]\[(.*?)\] (.*?)/isU', $errorSummerTitle, $result);

            $itemVars['errText']    = "[{$result[6]}]";
            $itemVars['exception']  = "[{$result[6]}]";
            $itemVars['time']       = strtotime("$result[1] $result[2]");
            $itemVars['ip']         = $result[3];
            $itemVars['stackTrace'] = '';
            $itemVars['data']       = $data;
        }else {
            preg_match('/(.*?) (.*?) \[(.*)\](.*?)\[(error|warning|info)\]\[(.*?)\] (.*?)/isU', $errorSummerTitle, $result);
    
            $errorinfo              = "[{$result[6]}] $result[7]";
            $itemVars['exception']  = "[{$result[6]}]";
            $itemVars['errText']    = $errorinfo;
            $itemVars['time']       = strtotime("$result[1] $result[2]");
            $itemVars['ip']         = $result[3];
            $itemVars['stackTrace'] = $errorSummer;
        }
        
        
        //请求系统变更
        $varCount = count($vars);
        for ($i = 1 ; $i < $varCount ; $i++) {
            list($k, $v) = explode(' = ', $vars[$i]);
            $varStr = explode("\n", trim($v));
            foreach ($varStr as &$line){
                $line = trim($line);
            }
            $varStr = array_filter($varStr);
            $varStr = join(",", $varStr);
            $varStr = str_replace('[,', '[', $varStr);
    
            //if($i==1){
            //    var_dump("\$$k = $varStr;");exit;
            //}
            eval("\$$k = $varStr;");
            $itemVars[strtolower($k)] = ${$k};
        }

        return $itemVars;
    }
    
    /**
     * 统计指定列不同项出现的次数
     *
     * @param $column
     *
     * @return array
     */
    public function groupStat($column) {
        $data  = $this->getModels();
        $stats = [];
        foreach ($data as $item) {
            if (isset($item[$column])) {
                $stats[$item[$column]] = isset($stats[$item[$column]]) ? $stats[$item[$column]] + 1 : 1;
            }
        }
        
        arsort($stats);
        
        return $stats;
    }
    
    /**
     * 获取单个错误详情
     * @param string $key
     *
     * @return array
     */
    public function findByPk(string $key):array
    {
        $data  = $this->getModels();
        foreach ($data as $item) {
            if($item['key']===$key){
                return $item;
            }
        }
        return [];
    }
    
    /**
     * 没有变量名的异常抛出
     * @param $line
     *
     * @return bool
     */
    public function isNoVarName($line){
        $line = trim($line);
        $res = preg_match('/^(.*?) (.*?) \[(.*)\] \[$/', $line, $result);
        return (bool)$res;
    }
    
    /**
     *  in /  格式的错误跟踪
     * @param $line
     *
     * @return bool
     */
    public function isInStack($line){
        $res = preg_match('/^in \/(.*?)$/', $line, $result);
        return (bool)$res;
    }
    
    /**
     * 错误迅信的描述行
     * @param $line
     *
     * @return bool
     */
    public function isErrorSummeryLine($line){
        $res = preg_match('/(.*?) (.*?) \[(.*)\](.*?)\[(error|warning|info)\]\[(.*?)\] (.*?)/isU', $line, $result);
        return (bool)$res;
    }
    
    /**
     * 错误信息变量申明行
     * @param $line
     *
     * @return bool
     */
    public function isInfoDespLine($line){
        $line = trim($line);
        $res = preg_match('/^(.*?) (.*?) \[(.*)\]$/', $line, $result);
        return (bool)$res;
    }
}
