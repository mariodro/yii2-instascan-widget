<?php

namespace mdq\instascan;

use yii\web\AssetBundle;
/**
 * Description of QrReaderAsset
 *
 * @author Mario Droguett <mariodro@hotmail.com>
 */
class QrReaderAsset extends AssetBundle{
    public $sourcePath = __DIR__.'/assets';
    public $css = [
        //'css/qrcodereader.css',
    ];
    public $js = [
        'js/lib/instascan.min.js',
        //'js/qrcode-reader.js',
    ]; 
}
