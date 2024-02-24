<?php

class EmailController extends CAdminController{
    
    /**
     * Выдает форму создания письма для e-mail рассылки
     */
    public function actionDelivery(){
        $cEmail = Yii::app()->email;
        $this->render("create",array(
            "template"=>$cEmail->render("{content}")
        ));
    }
    
    /**
     * Возвращает html-код письма, которое будет отправлено пользователю
     */
    public function actionDeliveryTest(){
        if (empty($_POST['text']) || !isset($_POST['title']))
            throw new CHttpException(400,"Не указан текст сообщения");
        $cEmail = Yii::app()->email;
        $cEmail->to = Yii::app()->user->getState("email");
        $cEmail->subject = $_POST['title'];
        if(!$cEmail->send($_POST['text']))
            throw new CHttpException(400,"Ошибка доставки");
    }
    
    /**
     * Отправляет письмо пользователям
     */
    public function actionDeliverySend(){
        
    }
}
?>
