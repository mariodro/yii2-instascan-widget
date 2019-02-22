<?php

namespace mdq\instascan;
use yii\helpers\Html;
use yii\helpers\JSon;
use yii\web\JsExpression;
use yii\helpers\ArrayHelper;

/**
 * Description of QrReader
 *
 * @author Mario Droguett <mariodro@hotmail.com>
 */
class QrReader extends \yii\base\Widget
{
    public $videoOptions = [];
    // When buttonLabel is "false", render camera immediately.
    public $buttonLabel = 'Scan';
    public $buttonOptions = [];
    public $options = [];
    
    public $successCallback;
    public $noCameraFoundCallback;
    public $initErrorCallback;
    
    // Whether to scan continuously for QR codes. If false, use scanner.scan() to manually scan.
    // If true, the scanner emits the "scan" event when a QR code is scanned. Default true.
    public $continuous = true;
    
    // Whether to horizontally mirror the video preview. This is helpful when trying to
    // scan a QR code with a user-facing camera. Default true.
    public $mirror = true;
    
    // Whether to include the scanned image data as part of the scan result. See the "scan" event
    // for image format details. Default false.
    public $captureImage = false;
    
    // Only applies to continuous mode. Whether to actively scan when the tab is not active.
    // When false, this reduces CPU usage when the tab is not active. Default true.
    public $backgroundScan = false;
    
    // Only applies to continuous mode. The period, in milliseconds, before the same QR code
    // will be recognized in succession. Default 5000 (5 seconds).
    public $refractoryPeriod = 5000;
    
    // Only applies to continuous mode. The period, in rendered frames, between scans. A lower scan period
    // increases CPU usage but makes scan response faster. Default 1 (i.e. analyze every frame).
    public $scanPeriod = 10;
    
    public $debug;
    
    public function init() {
        
        $this->buttonOptions = ArrayHelper::merge(['id' => $this->id.'-scan-btn', 'class' => 'btn btn-default'], $this->buttonOptions);
        
        $this->options['id'] = $this->id;
        $this->options = ArrayHelper::merge(['class' => 'qr-reader'], $this->options);
        
        $this->videoOptions = ArrayHelper::merge(['id' => $this->id."-preview", 'class' => 'qr-reader-preview', 'style' => 'width: 300px; height: 188px; '], $this->videoOptions);
        
        if ($this->debug === null)
        {
            $this->debug = YII_DEBUG;
        }
        
        if ($this->debug && $this->successCallback === null)
        {
            $this->successCallback = "function(data){console.log(data);}";
        }
        if ($this->debug && $this->noCameraFoundCallback === null)
        {
            $this->noCameraFoundCallback = "function(cameras){console.error('No cameras found.');}";
        }
        
        if ($this->debug && $this->initErrorCallback === null)
        {
            $this->initErrorCallback = "function (e) {console.error(e);}";
        }
        
        QrReaderAsset::register($this->getView());
    }
    
    public function run()
    {
        $this->registerJsOptions();
        $content = '';
        if ($this->buttonLabel !== false)
        {
            $content .= Html::Button($this->buttonLabel, $this->buttonOptions);
            $this->videoOptions['style'] .= '; display: none;';
        }
        
        $content .= Html::tag('video', '', $this->videoOptions);
        
        echo Html::tag('span', $content, [
            'id' => $this->id,
            'class' => 'qr-reader',
        ]);
        
    }
    
    protected function registerJsOptions(){
        $id = $this->id;
        $videoId = $this->videoOptions['id'];
        
        $jsvar = str_replace('-', '', $id);
        $options = Json::encode([
            'video' => new JsExpression("document.getElementById('$id-preview')"),
            'continuous' => $this->continuous,
            'mirror' => $this->mirror,
            'captureImage' => $this->captureImage,
            'backgroundScan' => $this->backgroundScan,
            'refractoryPeriod' => $this->refractoryPeriod,
            'scanPeriod' => $this->scanPeriod, 
        ]);
        
        $successCallback = $this->successCallback ? "{$jsvar}_scanner.addListener('scan', {$this->successCallback});" : '';
        $noCameraFoundCallback = $this->noCameraFoundCallback !== null ? '('.$this->noCameraFoundCallback.')(cameras);' : '';
        $initErrorCallback = $this->initErrorCallback !== null ? '('.$this->initErrorCallback.')(e);' : '';
        
        $buttonEvent = $this->buttonLabel !== false ?
<<<JS
$('#{$this->buttonOptions['id']}').on('click', function(){
    $(this).hide();
    $('#$videoId').show();
    {$jsvar}_init();
});
JS
        : '';
    
        $buttonOnScanListener = $this->buttonLabel !== false ?
<<<JS
    {$jsvar}_scanner.addListener('scan', function(data){
        {$jsvar}_scanner.stop();
        $('#$videoId').hide();
    });
JS
        : '';
        
        $view = $this->getView();
        $view->registerJs(
<<<JS
let {$jsvar}_scanner;
{$jsvar}_init = function() {
    if (typeof {$jsvar}_scanner == 'undefined')
    {
        {$jsvar}_scanner = new Instascan.Scanner($options);
        $buttonOnScanListener
        $successCallback;
        window.scan = {$jsvar}_scanner;
    }
    Instascan.Camera.getCameras().then(function (cameras) {
      if (cameras.length > 0) {
        {$jsvar}_scanner.start(cameras[0]);
      } else {
        $noCameraFoundCallback
      }
    }).catch(function (e) {
      $initErrorCallback
    });
};
$buttonEvent  
JS
        );
    }
}
