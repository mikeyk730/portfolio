<?php
namespace app\assets;
use yii\web\AssetBundle;

class AlbumAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
       'main/user.css',
       'main/styles.css',
       'main/skin-styles.css',
    ];
    public $js = [
       '//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js',
       'main/scripts.js',
    ];
    public $depends = [
    ];
}