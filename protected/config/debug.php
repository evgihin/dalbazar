<?php
$config['name'] = 'Дальбазар.ру';
if (YII_DEBUG)
    $config['name'] .= ' (режим отладки)';
$config['modules']['gii'] = array(
            'class' => 'system.gii.GiiModule',
            'password' => false,
        // If removed, Gii defaults to localhost only. Edit carefully to taste.
        //'ipFilters' => array('127.0.0.1', '::1'),
        );
$config['components']['db'] = array(
            'class' => 'UCDbConnection',
            'connectionString' => 'mysql:host=localhost;dbname=dalbazar',
            'emulatePrepare' => true,
            'username' => 'test',
            'password' => 'test',
            'charset' => 'utf8',
            'tablePrefix' => '',
            'autoConnect' => false,
            // показываем значения параметров
            'enableParamLogging' => true,
            // включаем профайлер
            'enableProfiling' => true,
        );
$config['components']['email']['delivery'] = "php";
$config['params']['smsSimulationMode']=YII_DEBUG;