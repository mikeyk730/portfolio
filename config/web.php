<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'mkaminski_exposed',
    'name' => '{m.kaminski}',
    'defaultRoute' => 'album/home',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'devicedetect'],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'er4MgvjWXyIYppEy-Ge9Tgw_tGrEZunn',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
            'transport' => require(__DIR__ . '/mailer_transport.php'),
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
           'enablePrettyUrl' => true,
           'showScriptName' => false,
           'rules' => array(
               '<controller:\w+>/<id:\d+>' => '<controller>/view',
               '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
               '<controller:\w+>/<action:\w+>/<url_text:[\w\-]+>' => '<controller>/<action>',
               '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
               '<controller:\w+>' => '<controller>/index',
           ),
        ],
        'utility' => [
           'class' => 'app\components\Utility'
        ],
        'image' => array(
           'class' => 'yii\image\ImageDriver',
           'driver' => 'GD',  //GD or Imagick
        ),
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
        ],
        'devicedetect' => [
            'class' => 'alexandernst\devicedetect\DeviceDetect'
        ],
        'db' => require(__DIR__ . '/db.php'),
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;
