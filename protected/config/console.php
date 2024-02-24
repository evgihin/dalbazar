<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'Дальбазар.ру',
    'charset' => 'utf-8',
    'language' => 'ru',
    'import' => array(
        'application.models.*',
        'application.components.*',
    ),
    // preloading 'log' component
    'preload' => array('log'),
    // application components
    'components' => array(
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ),
            ),
        ),
        'email' => array(
            'class' => 'application.extensions.email.Email',
            'delivery' => 'php',
            'from' => 'noreply@dalbazar.ru',
            'replyTo' => 'post@dalbazar.ru',
            'layout' => 'main', //отображение по умолчанию, лежит в папке application.views.email.layouts
            'maxRecipients' => 5,
            'mailsPerTakt' => 5,
        ),
    ),
    'params' => array()
);