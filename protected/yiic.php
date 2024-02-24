<?php
mb_internal_encoding("UTF-8");

// change the following paths if necessary
$yiic=dirname(__FILE__).'/../framework/yiic.php';
$confpath['main']=dirname(__FILE__).'/config/console.php';
$confpath['debug']=dirname(__FILE__).'/config/debug.php';
$confpath['production']=dirname(__FILE__).'/config/production.php';

defined('YII_DEBUG') or define('YII_DEBUG', file_exists("../.debug"));
$config = require($confpath['main']);
if (!file_exists("../.production"))
    require($confpath['debug']);
else
    require($confpath['production']);

require_once($yiic);
