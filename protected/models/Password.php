<?php

class Password extends CFormModel {

    /**
     * Генерирует пароль для e-mail письма
     */
    function generateEmail() {
        $chars = "abcdefghikmnpqrstuvwxyz123456789";
        $max = 10;
        $size = StrLen($chars) - 1;

        // Определяем пустую переменную, в которую и будем записывать символы. 
        $password = null;

        // Создаём пароль. 
        while ($max--)
            $password.=$chars[rand(0, $size)];
        return $password;
    }

    /**
     * Генерирует пароль для sms-сообщения
     */
    function generateSms() {
        $chars = "abcdefgh123456789";
        $max = Yii::app()->params['smsCodeLength'];
        $size = StrLen($chars) - 1;

        // Определяем пустую переменную, в которую и будем записывать символы. 
        $password = null;

        // Создаём пароль. 
        while ($max--)
            $password.=$chars[rand(0, $size)];
        return $password;
    }
    
    function check($pass,$userId=NULL){
        if (is_null($userId))
            $userId = Yii::app()->user->id;
        $cUser = new User();
        $user = $cUser->getByUser($userId);
        return $user['pass']!='' && $user['pass']===Helpers::hash($pass);
    }
    
    /* Функции с приставкой recovery работают с донглами паролей (временный пароль, действующий сутки). 
     * Донгл генерится после отправки восстановления пароля либо по СМС. */
    
    /**
     * Генерирует донгл для пользователя и сохраняет его в базе данных
     * @param int $userId
     */
     function recoveryGenerate($userId=NULL){
         if (is_null($userId))
            $userId = Yii::app()->user->id;
         $dongle = Helpers::hash($this->generateEmail())."|".time();
         Yii::app()->db->createCommand()
                 ->update('user', array('dongle'=>$dongle), 'user_id=:uid',array(":uid"=>$userId));
     }
     
     /**
      * Проверяет донгл пользователя
      * @param string $dongle
      * @param int $userId
      */
     function recoveryCheck($dongle,$userId=NULL){
         if (is_null($userId))
            $userId = Yii::app()->user->id;
         $key = $this->recoveryGet($userId);
         
         if (!$key || $key!==$dongle)
             return false;
         
         return true;
     }
     
     function recoveryGet($userId=NULL){
         if (is_null($userId))
            $userId = Yii::app()->user->id;
         $str = Yii::app()->db->createCommand()
                 ->select('dongle')
                 ->from('user')
                 ->where('user_id=?',array($userId))
                 ->queryScalar();
         if (!$str) return false;
         $str = preg_split("/\|/", $str);
         $time = (int)$str[1];
         $key = $str[0];
         //сколько времени прошло после генерации
         if (time()-$time>86400)
             return false;
         
         return $key;
     }
     
     function recoveryRemove($userId=NULL){
         if (is_null($userId))
            $userId = Yii::app()->user->id;
         Yii::app()->db->createCommand()
                 ->update('user', array('dongle'=>""), 'user_id=:uid',array(":uid"=>$userId));
     }

}

?>
