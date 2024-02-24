<?php
// Все функции выполняются регулярно и возвращают количество секунд до следующего выполнения
class RegularRun extends CFormModel {
    
    //Отключает объявления из выдачи, если их срок действия закончился
    function expireAdvert(){
        $ids =  Yii::app()->db->createCommand()
                ->select("advert_id")
                ->from("advert")
                ->where('expirate_time<=UNIX_TIMESTAMP()')
                ->queryColumn();
        Yii::app()->db->createCommand()
                ->update('advert', array(
                    'active'=>0
                ),array("in","advert_id",$ids));
        foreach($ids as $id){
            State::update($id, "expired", "Отключено роботом в ".date("d.m.Y H:i"));
        }
        
        return 60*60*24;//раз в сутки
    }
    
    function sendEmail(){
        /* @var $email Email */
        $email = Yii::app()->email;
        $email->sendQueue();
        return 60;//раз в минуту
    }
}
?>
