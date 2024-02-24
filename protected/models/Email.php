<?php

class Email extends CFormModel {
    
    public $news = false;
    public $notifications = false;
    
    public function rules() {
        return array(
            array('news, notifications',"boolean",'on'=>'edit')
        );
    }
    
    /**
     * Обновить подписку пользователя
     */
    public function updateSubscription($userId = NULL){
        if (is_null($userId)){
            $userId = Yii::app()->user->id;
            Yii::app()->user->setState("recieve_email_notifications",(int)$this->notifications);
            Yii::app()->user->setState("recieve_email_news",(int)$this->news);
        }
        Yii::app()->db->createCommand()
                ->update('user', array(
                    "recieve_email_notifications"=>(int)$this->notifications,
                    "recieve_email_news"=>(int)$this->news
                ),"user_id=:uid", array(":uid" => $userId));
       
    }

    public function unsucscribeNotifications($email) {
        $cUser = new User();
        $user = $cUser->getByEmail($email);
        Yii::app()->db->createCommand()
                ->update("user", array("recieve_email_notifications"=>0), "user_id=:uid", array(":uid" => $user['user_id']));
    }

    public function unsucscribeNews($email) {
        $cUser = new User();
        $user = $cUser->getByEmail($email);
        Yii::app()->db->createCommand()
                ->update("user", array("recieve_email_news"=>0), "user_id=:uid", array(":uid" => $user['user_id']));
    }

    public static function regExp($javaScript = false) {
        $cEmail = new CEmailValidator();
        //регулярка принимает два формата:
        //1. +7 (999) 999-9999
        //2. 9999999999 (короткий, если маска ввода не сработала)
        //3. 79999999999 (если нужен 7)
        $exp = $cEmail->pattern;
        if (!$javaScript)
            $exp.="u";
        return $exp;
    }
}
