<?php

$config['components']['db'] = array(
            'class' => 'UCDbConnection',
            'connectionString' => 'mysql:host=localhost;dbname=dalbazar',
            'emulatePrepare' => true,
            'username' => 'asdedrgdfs',
            'password' => '2s9Rr8P2WhNXNs0qOgzG',
            'charset' => 'utf8',
            'tablePrefix' => '',
            'autoConnect' => false,
            // РїРѕРєР°Р·С‹РІР°РµРј Р·РЅР°С‡РµРЅРёСЏ РїР°СЂР°РјРµС‚СЂРѕРІ
            'enableParamLogging' => true,
            // РІРєР»СЋС‡Р°РµРј РїСЂРѕС„Р°Р№Р»РµСЂ
            'enableProfiling' => true,
        );
$config['params']['smsSimulationMode']=false;
