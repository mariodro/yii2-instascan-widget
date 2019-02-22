Yii2 Instascan Widget
=====================
Yii2 widget for reading qr code using instascan pluggin
https://github.com/schmich/instascan

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist mdq/yii2-instascan-widget "*"
```

or add

```
"mdq/yii2-instascan-widget": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= QrReader::widget([
        'readerWidth' = '300px',
        'readerHeight' = '188px',
        'buttonLabel' = 'Scan',
        'buttonOptions' = [],
        'options' = [],
    
        'successCallback' = 'function(data){console.log(data);}',

        // Whether to scan continuously for QR codes. If false, use scanner.scan() to manually scan.
        // If true, the scanner emits the "scan" event when a QR code is scanned. Default true.
        'continuous' = true,

        // Whether to horizontally mirror the video preview. This is helpful when trying to
        // scan a QR code with a user-facing camera. Default true.
        'mirror' = true,

        // Whether to include the scanned image data as part of the scan result. See the "scan" event
        // for image format details. Default false.
        'captureImage' = false,

        // Only applies to continuous mode. Whether to actively scan when the tab is not active.
        // When false, this reduces CPU usage when the tab is not active. Default true.
        'backgroundScan' = false,

        // Only applies to continuous mode. The period, in milliseconds, before the same QR code
        // will be recognized in succession. Default 5000 (5 seconds).
        'refractoryPeriod' = 5000,

        // Only applies to continuous mode. The period, in rendered frames, between scans. A lower scan period
        // increases CPU usage but makes scan response faster. Default 1 (i.e. analyze every frame).
        'scanPeriod' = 10,
    ]); ?>```