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
    public $readerWidth = '300px';
    public $readerHeight = '188px';
    public $buttonLabel = 'Scan';
    public $buttonOptions = [];
    public $options = [];
    
    public $successCallback = 'function(data){console.log(data);}';
    
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
    
    
    public function init() {
        
        $button_id = ArrayHelper::getValue($this->buttonOptions, 'id', $this->id.'-scan-btn');
        $this->buttonOptions = ArrayHelper::merge(['id' => $button_id, 'class' => 'btn btn-default'], $this->buttonOptions);
        
        $this->options['id'] = $this->id;
        $this->options = ArrayHelper::merge(['class' => 'qr-reader'], $this->options);
        
        QrReaderAsset::register($this->getView());
    }
    
    public function run()
    {
        $this->registerJsOptions();
        
        $content = Html::Button($this->buttonLabel, $this->buttonOptions);
        
        $content .= Html::tag('video', '', [
            'id' => $this->id."-preview",
            'class' => 'qr-reader-preview',
            'style' => "display: block; width:$this->readerWidth; height:$this->readerHeight; display: none;",
        ]);
        
        echo Html::tag('span', $content, [
            'id' => $this->id,
            'class' => 'qr-reader',
        ]);
        
    }
    
    protected function registerJsOptions(){
        $id = $this->id;
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
                                
        $view = $this->getView();
        $view->registerJs(
<<<JS
let {$jsvar}_scanner;
$('#{$this->buttonOptions['id']}').on('click', function(){
    $(this).hide();
    $('#$id-preview').show();
    
    if (typeof {$jsvar}_scanner == 'undefined')
    {
        {$jsvar}_scanner = new Instascan.Scanner($options);
        {$jsvar}_scanner.addListener('scan', function(data){
            {$jsvar}_scanner.stop();
            $('#$id-preview').hide();
        });
        {$jsvar}_scanner.addListener('scan', {$this->successCallback});
        window.scan = {$jsvar}_scanner;
    }
    Instascan.Camera.getCameras().then(function (cameras) {
      if (cameras.length > 0) {
        {$jsvar}_scanner.start(cameras[0]);
      } else {
        console.error('No cameras found.');
      }
    }).catch(function (e) {
      console.error(e);
    });
        
});
JS
        );
    }
}
