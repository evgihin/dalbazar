<?php

class Debug extends CWidget {

    public function run() {
        if (Yii::app()->user->hasState('d_email')) {
            //register css file
            $url = CHtml::asset(Yii::getPathOfAlias('application.extensions.email.css.debug') . '.css');
            Yii::app()->getClientScript()->registerCssFile($url);

            //dump debug info
            $mails = Yii::app()->user->getState('d_email');
            foreach ($mails as $mail) {
                echo $mail . "<hr>";
            }
            //Yii::app()->user->setFlash('email', null);
        }
    }

}