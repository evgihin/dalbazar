<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'Дальбазар.ру',
    'defaultController' => 'site',
    'language' => 'ru',
    'sourceLanguage' => 'ru',
    'charset' => 'utf-8',
    'layout' => 'main',
    // preloading 'log' component
    'preload' => array('log'),
    // autoloading model and component classes
    'import' => array(
        'application.models.*',
        'application.components.*',
    ),
    // application components
    'components' => array(
        'user' => array(
            // enable cookie-based authentication
            'allowAutoLogin' => false,
            'loginUrl' => array('login/index'),
            'autoRenewCookie' => true,
            'stateKeyPrefix' => '_', //по умолчанию используется сильно длинный md5 префикс, который ваще не экономит место
        ),
        // uncomment the following to enable URLs in path-format
        'urlManager' => array(
            'urlFormat' => 'path',
            /* 'rules' => array(
              'gii' => 'gii',
              'gii/<controller:\w+>' => 'gii/<controller>',
              'gii/<controller:\w+>/<action:\w+>' => 'gii/<controller>/<action>',
              ), */
            'rules' => array(
                'category/filter/<categoryId:\d+>' => 'category/filter',
                'category/allParams/<filter_id:\d+>' => 'category/allParams',
                'category/setAllParams' => 'category/setAllParams',
                'category/updateDependParams' => 'category/updateDependParams',
                'category/<alias1:\w+>/<alias2:\w+>/<alias3:\w+>' => 'category/show',
                'category/<alias1:\w+>/<alias2:\w+>' => 'category/show',
                'category/<alias1:\w+>' => 'category/show',
                'category' => 'category/show',
                //'filter/getForId/<id:\d+>' => 'filter/getForId',
                //'advert/<alias:\w+>/*' => 'advert/show',
                'image/show/<width:\d+>_<file:[a-zA-Z0-9_\-\.]+\.[a-zA-Z]{1,5}>' => array('image/show', 'urlSuffix' => '', 'caseSensitive' => false), //@todo проверить, можно ли удалить старый механизм генерации картинок
                'image/show/<width:\d+>_{0,1}' => array('image/show', 'urlSuffix' => '', 'caseSensitive' => false),
                'admin' => 'admin/main/index',
                'panel' => 'panel/site/index',
                'logout' => 'login/logout',
                'bug' => 'site/bug',
                'advancedSearch' => "search/advanced",
                'search' => "search/result"
            ),
            'showScriptName' => false,
            'urlSuffix' => '.aspx',
        ),
        /*
          'db'=>array(
          'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
          ), */
        // uncomment the following to use a MySQL database
        'errorHandler' => array(
            // use 'site/error' action to display errors
            'errorAction' => 'site/error',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'enabled' => YII_DEBUG,
            'routes' => array(
//                array(
//                    // направляем результаты профайлинга в ProfileLogRoute (отображается
//                    // внизу страницы)
//                    'class' => 'CProfileLogRoute',
//                    'levels' => 'profile',
//                    'enabled' => true,
//                ),
//                array(
//                    'class' => 'CFileLogRoute',
//                    'levels' => 'error, warning',
//                ),
//                array(
//                    'class' => 'ext.yii-debug-toolbar.YiiDebugToolbarRoute',
//                    'ipFilters' => array('127.0.0.1', '192.168.0.9'),
//                ),
            // uncomment the following to show log messages on web pages
              "cweb" => array(
              'class'=>'CWebLogRoute',
              ),
            ),
        ),
        'session' => array(
            'class' => 'application.components.UCDbHttpSession',
            'sessionTableName' => 'session',
            'autoCreateSessionTable' => false,
            'connectionID' => 'db',
            'autoStart' => 'true',
            'timeout' => 86400 * 365,
            'cookieParams' => array('lifetime' => 86400 * 365),
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
    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array(
        // this is used in contact page
        'adminEmail' => 'admin@dalbazar.ru',
        'advertLifeTime' =>  (86400 * 30), //время жизни объявления по умолчанию
        'countMenuItems' => 10, //Количество пунктов под кнопкой "добавить объявление" в шапке. Нужно для модуля URecentCategories
        'countLastAdvert' => 7, //Количество последних добавленных объявлений, отображаемое на главной странице
        'countSubPerBlock' => 10, //Количество подкатегорий в блоке на главной странице
        'countTopFilters' => 3, //количество топовых фильтров, которые можно отобразить на странице
        'updateSIDTables' => array(), //список таблиц, в которых надо обновить ИД сессии (поле sid) при входе либо выходе с сайта
        'tempAdvertImagesStorageTime' => 86400 * 30, //срок хранения временных картинок при добавлении объявления
        'advertImagesStoragePath' => 'images/advert/', //папка хранения картинок объявлений
        'pageSizeValues' => array(10, 20, 50, 100), //параметры размеров страниц
        'topAdvertTime' => 5, //Сколько часов хранится прикрепленное объявление на странице с категорией
        'needModerateAfterPublish' => false, //нужно ли отдавать на модерацию сразу после публикации
        //настройки отправки СМС
        'smsSimulationMode' => false, //Функция отправки начинает работать в режиме симуляции, т.е. фактически не отправляет сообщения
        //Подтверждение номера телефона по СМС
        'maxSMSConfirmationsPerPhone' => 5, //Количество повторных отправок СМС пользователю
        'maxSMSConfirmationsPerUser' => 20, //Количество переотправок для пользователя
        'smsPerPhoneStoreTime' => 60 * 60 * 24, //24 часа //время работы ограничений для телефона
        'smsPerUserStoreTime' => 60 * 60 * 24 * 3, //3 дня //время работы ограничений для пользователя
        'smsCodeLength' => 5, //длина в символах кода отправляемого в СМС
        'smsCodeText' => "Код подтверждения Вашего телефона: %s", //Текст для отправки кода подтверждения
        'maxConfirmationWaitingTime' => 60 * 60 * 3, //3 часа //максимальное время жизни кода подтверждения
    ),
);