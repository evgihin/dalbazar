<?php

class TestController extends CFrontEndController {

    public function actionIndex() {
        $cPassword = new Password();
        //$cPassword->recoveryGenerate();
        var_dump($cPassword->recoveryGet());
    }

    public function actionEmail() {
        $this->widget('application.extensions.email.debug');
    }

    public function actionCreateMale() {
        /* @var $email Email */
        $email = Yii::app()->email;
        for ($i = 0; $i < 100; $i++) {
            $email->to[] = $i . '_test1@mail.ru';
        }
        $email->subject = 'test1';
        $email->send("test text");
    }

    public function actionProcessMale() {
        $reg = new RegularRun();
        $reg->sendEmail();
    }

    public function actionGenPass() {
        echo Helpers::hash("15928475Sfa");
    }

    public function actionAddCat() {
        Yii::app()->db->createCommand()
                ->insert("farpost_category", array(
                    "name" => $_GET['name'],
                    "link" => $_GET["link"],
                    "farpost_category_parent_id" => $_GET["parent"],
                    "pos" => 0
        ));
        echo "true";
    }

}
