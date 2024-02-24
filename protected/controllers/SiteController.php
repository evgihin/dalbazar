<?php

class SiteController extends CFrontEndController {

    public $left;

    public function actions() {
        return array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
            ),
            // page action renders "static" pages stored under 'protected/views/site/pages'
            // They can be accessed via: index.php?r=site/page&view=FileName
            'page' => array(
                'class' => 'CViewAction',
            ),
        );
    }

    /**
     * Главная страница сайта. Может получать рекламные данные от пользователей
     */
    public function actionIndex() {
        $category = new Category();
        //Получаем главные категории
        $categories = $category->get(NULL, 0);

        //получаем список подкатегорий
        $ids = $this->getIdArray($categories, 'category_id');
        $subCategories = $category->get($ids, 1, true);

        $this->left = $this->renderPartial('indexLeft', array(
            'categories' => $categories,
            'subCategories' => $subCategories,
            'top' => $category->getTopProducts($ids),
                ), true);

        
        $arr = array();
        //получаем последние 20 объявлений
        $lastest = Advert::getLastestWithImage(20);
        foreach ($lastest as $val) {
            $arr['lastest'][] = array(
                'img' => Helpers::getImageUrl($val['image'], 120, 120),
                'description' => $val['zagolovok'],
                'href' => $this->createUrl('advert/show', array('advert_id' => $val['advert_id'])),
            );
        }
        
        //дублируем последние для бокового меню (новое!)
        $this->right = array();
        foreach ($lastest as $val) {
            $this->right[] = array(
                'img' => Helpers::getImageUrl($val['image'], 120, 120),
                'description' => $val['zagolovok'],
                'href' => $this->createUrl('advert/show', array('advert_id' => $val['advert_id'])),
                'price' => $val['price'],
            );
        }

        //получаем ТОП-20 по количеству просмотров
        $topViews = Advert::getTopViewsWithImage(20);
        foreach ($topViews as $val) {
            $arr['topViews'][] = array(
                'img' => Helpers::getImageUrl($val['image'], 120, 120),
                'description' => $val['zagolovok'],
                'href' => $this->createUrl('advert/show', array('advert_id' => $val['advert_id'])),
            );
        }

        $this->render('index',$arr);
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError() {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }

    /**
     * Displays the contact page
     */
    public function actionContact() {
        $model = new ContactForm();
        if (isset($_POST['ContactForm'])) {
            $model->attributes = $_POST['ContactForm'];
            if ($model->validate()) {
                $name = '=?UTF-8?B?' . base64_encode($model->name) . '?=';
                $subject = '=?UTF-8?B?' . base64_encode($model->subject) . '?=';
                $headers = "From: $name <{$model->email}>\r\n" .
                        "Reply-To: {$model->email}\r\n" .
                        "MIME-Version: 1.0\r\n" .
                        "Content-type: text/plain; charset=UTF-8";

                mail(Yii::app()->params['adminEmail'], $subject, $model->body, $headers);
                Yii::app()->user->setFlash('contact', 'Thank you for contacting us. We will respond to you as soon as possible.');
                $this->refresh();
            }
        }
        $this->render('contact', array('model' => $model));
    }

    public function actionBug() {
        $this->render('getBug');
    }

    public function actionInsertBug() {
        if (isset($_POST['link']) && isset($_POST['text'])) {
            Yii::app()->db->createCommand()
                    ->insert('bug', array(
                        'link' => $_POST['link'],
                        'text' => $_POST['text'],
                        'creation' => time(),
                        'user_agent' => Yii::app()->request->userAgent,
                        'ip' => Yii::app()->request->userHostAddress,
            ));
            
            /* @var $email Email */
            $email = Yii::app()->email;
            $email->to = Yii::app()->params['adminEmail'];
            $email->view = "newBug";
            $email->send(array("bugId"=>Yii::app()->db->lastInsertID));
        }
        $this->render('insertBug');
    }
    
    public function actionYandexTableau(){
        echo CJSON::encode(array(
            "version" => "0.0.1",
            "api_version" => 1,
            "layout" => array(
                "logo" => "/images/theme/logo_small.png",
                "color" => "#ffffff",
                "show_title" => false
            )
        ));
        Helpers::disableWebLogRoute();
    }

}