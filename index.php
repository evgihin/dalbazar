<?php

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG', file_exists(".debug"));

//error_reporting(E_ALL ^ E_NOTICE);
if (YII_DEBUG)
    error_reporting(E_ALL);
else 
    error_reporting(E_ERROR);
mb_internal_encoding("UTF-8");

// change the following paths if necessary
$yii = dirname(__FILE__) . '/framework/yii.php';

$confpath['main'] = dirname(__FILE__) . '/protected/config/main.php'; //основные настройки
$confpath['production'] = dirname(__FILE__) . '/protected/config/production.php'; //допнастройки 
$confpath['debug'] = dirname(__FILE__) . '/protected/config/debug.php'; //основные настройки


// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

require_once($yii);
$config = require($confpath['main']);
if (!file_exists(".production"))
    require($confpath['debug']);
else
    require($confpath['production']);

Yii::createWebApplication($config)->run();
