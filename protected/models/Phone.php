<?php

class Phone extends CExtFormModel {
    
    public function getById($phoneId){
        return Yii::app()->db->createCommand()
                        ->select()
                        ->from("phone")
                        ->where("phone_id=:pid", array(":pid" => $phoneId))
                        ->limit(1)
                        ->queryRow();
    }

    public static function getByUser($userId) {
        return Yii::app()->db->createCommand()
                        ->select()
                        ->from("phone")
                        ->where("user_id=:uid", array(":uid" => $userId))
                        ->order("primary DESC")
                        ->queryAll();
    }

    //возвращает массив с телефонами для объявления. Добавляется параметр "temp", который true если телефон временный
    public static function getByAdvert($advertId, $temp = NULL) {
        $req = Yii::app()->db->createCommand()
                ->select()
                ->from("advert_phones")
                ->where("advert_id=:aid", array(":aid" => $advertId))
                ->order("temp ASC");
        if (!is_null($temp) && is_bool($temp))
            $req->where_and("temp=:t", array(":t" => $temp));
        return $req->queryAll();
    }
    
    /**
     * Получает количество телефонов, хранящихся в учетной записи
     * @param int $userId ИД пользователя, если NULL то ID берется из текущей сесси
     * @return int Количество телефонов в учетной записи
     */
    public function count($userId = NULL){
        if (is_null($userId))
            $userId = Yii::app()->user->getId();
        
        return Yii::app()->db->createCommand()
                        ->select()
                        ->from("phone")
                        ->where("user_id=:uid", array(":uid" => $userId))
                        ->order("primary DESC")
                ->query()
                ->count();
    }

    //сколько еще разрешено СМС подтверждений для указанного телефона/пользователя
    public function reminingConfirmations($phone, $userId = NULL, $sessionId = NULL) {
        $phone = Phone::simplify($phone);
        //сначала проверяем количество для телефона
        $reminingPhone = Yii::app()->db->createCommand("
            SELECT COUNT(*) FROM phone_confirmation as a 
            WHERE 
                a.phone=:phone AND 
                a.confirmed=0 AND
                a.date >= COALESCE(
                GREATEST(
                    (SELECT b.date FROM phone_confirmation as b WHERE b.phone=:phone AND b.confirmed=1 ORDER BY b.date DESC LIMIT 1)
                    ,:time), :time)
            ")->queryScalar(array(
            ":time" => time() - Yii::app()->params['smsPerPhoneStoreTime'],
            ":phone" => Phone::simplify($phone)
        ));
        $reminingPhone = Yii::app()->params['maxSMSConfirmationsPerPhone'] - $reminingPhone;
        if ($reminingPhone < 0)
            $reminingPhone = 0;

        //затем проверяем количество для пользователя либо для сессии (в зависимости от залогиннености
        if ($userId !== NULL) {
            $comm = Yii::app()->db->createCommand("
                SELECT COUNT(*) FROM phone_confirmation as a 
                WHERE 
                    a.user_id=:uid AND 
                    a.confirmed=0 AND
                    a.date >= COALESCE(
                    GREATEST(
                        (SELECT b.date FROM phone_confirmation as b WHERE b.user_id=:uid AND b.confirmed=1 ORDER BY b.date DESC LIMIT 1)
                        ,:time), :time)
                ");
            $comm->bindValue(":uid", Yii::app()->user->getId());
        } elseif ($sessionId !== NULL) {
            $comm = Yii::app()->db->createCommand("
                SELECT COUNT(*) FROM phone_confirmation as a 
                WHERE 
                    a.session_id=:sid AND 
                    a.confirmed=0 AND
                    a.date >= COALESCE(
                    GREATEST(
                        (SELECT b.date FROM phone_confirmation as b WHERE b.session_id=:sid AND b.confirmed=1 ORDER BY b.date DESC LIMIT 1)
                        ,:time), :time)
                ");
            $comm->bindValue(":sid", Yii::app()->session->sessionID);
        }
        else
            return $reminingPhone;

        $comm->bindValue(":time", time() - Yii::app()->params['smsPerUserStoreTime']);
        $reminingUser = Yii::app()->params['maxSMSConfirmationsPerUser'] - $comm->queryScalar();
        if ($reminingUser < 0)
            $reminingUser = 0;

        return min(array($reminingPhone, $reminingUser));
    }

    //Отправляет СМС с кодом подтверждения пользователю
    public function requestConfirmation($phone, $userId = NULL, $sessionId = NULL) {
        $phone = Phone::simplify($phone);

        //проверяем, можно ли пользователю проверить еще один телефон
        $remining = $this->reminingConfirmations($phone, $userId, $sessionId);
        if ($remining <= 0)
            return false;

        //далее генерируем пароль и отправляем в СМС
        $cPass = new Password();
        $smsCode = $cPass->generateSms();

        if (($answer = Sms::send($phone, sprintf(Yii::app()->params['smsCodeText'], $smsCode))) !== false) {
            $this->_registerConfirmation($phone, $smsCode, $userId, $sessionId);
        }
        return $answer;
    }

    //регистрирует запрос на подтверждение телефона в базе данных
    private function _registerConfirmation($phone, $smsCode, $userId = NULL, $sessionId = NULL) {
        Yii::app()->db->createCommand()
                ->insert("phone_confirmation", array(
                    "user_id" => $userId,
                    "phone" => $phone,
                    "date" => time(),
                    "code" => $smsCode,
                    "confirmed" => 0,
                    "session_id" => $sessionId
        ));
    }

    //проверяет код, отправленный ранее пользователю, возвращает true или false
    public function checkConfirmation($phone, $smsCode, $userId = NULL, $sessionId = NULL, $setConfirmed = true) {
        if (is_null($userId) && is_null($sessionId))
            return false;
        $phone = Phone::simplify($phone);

        $comm = Yii::app()->db->createCommand()->select()
                ->from("phone_confirmation")
                ->where(array("and", "phone=:phone", "date>=:d"), array(
                    ":phone" => $phone,
                    ":d" => time() - Yii::app()->params['maxConfirmationWaitingTime']
                ))
                ->order("date DESC")
                ->limit(1);
        if ($userId !== NULL)
            $comm->where_and("user_id=:uid", array(":uid" => $userId));

        if ($sessionId !== NULL)
            $comm->where_and("session_id=:sid", array(":sid" => $sessionId));
        $res = $comm->queryRow();
        if ($res != false && $res['confirmed']==0 && $res['code'] == $smsCode) {
            //сразу отмечаем что подтверждение прошло успешно
            if ($setConfirmed)
                $this->setConfirmed($res['confirmation_id']);
            return $res['confirmation_id'];
        }
        else
            return false;
    }

    //устанавливаем запрос как подтвержденный, возвращает ИД подтверждения либо false при ошибке
    public function setConfirmed($confirmationId) {
            Yii::app()->db->createCommand()
                    ->update("phone_confirmation", array("confirmed" => 1), "confirmation_id=:cid", array(":cid" => $confirmationId));
    }

    public static function add($userId, $phone, $primary = false) {
        Yii::app()->db->createCommand()->insert("phone", array(
            'user_id' => $userId,
            'phone' => Phone::format($phone),
            'phone_digits' => Phone::simplify($phone)
        ));
    }

    //прикрепить телефон к объявлению как дополнительный
    public function attachToAdvert($advertId, $phone, $temp = true) {
        Yii::app()->db->createCommand()->insert("advert_phones", array(
            'advert_id' => $advertId,
            'phone' => $phone,
            'phone_digits' => Phone::simplify($phone),
            'pos' => 0,
            'temp' => $temp
        ));
    }

    private $_userPhones = array();

    public function checkOwner($phone, $userId = null) {
        $phone = Phone::simplify($phone);
        if (is_null($userId))
            $userId = Yii::app()->user->id;
        //подобие кэширования для проверки сразу нескольких номеров
        if (empty($this->_userPhones[$userId])) {
            $this->_userPhones[$userId] = Phone::getByUser($userId);
        }
        foreach ($this->_userPhones[$userId] as $column) {
            if ($column['phone_digits'] == $phone)
                return true;
        }
        return false;
    }
    
    public function checkOwnerById($phoneId, $userId = null) {
        if (is_null($userId))
            $userId = Yii::app()->user->id;
        //подобие кэширования для проверки сразу нескольких номеров
        if (empty($this->_userPhones[$userId])) {
            $this->_userPhones[$userId] = Phone::getByUser($userId);
        }
        
        foreach ($this->_userPhones[$userId] as $column) {
            if ($column['phone_id'] == $phoneId)
                return true;
        }
        return false;
    }

    /**
     * Устанавливает основной телефон (может понадобится)
     * @param type $userId
     * @param type $phone
     * @return bool false если телефона не найдено и true если все прошло успешно
     */
    public static function setPrimary($userId, $phone) {
        $phone = Phone::simplify($phone);

        //сначала проверяем, есть ли телефон в базе данных
        $count = Yii::app()->db->createCommand()->select()->from("phone")->where("user_id=:uid AND phone_digits=:p", array(":uid" => $userId, ":p" => $phone))
                        ->query()->getRowCount();
        if ($count == 0)
            return false;


        //ставим все телефоны в 0
        Yii::app()->db->createCommand()->update("phone", array("primary" => 0), "user_id=:uid", array(":uid" => $userId));

        //ставим нужный ТЕЛ в 1
        Yii::app()->db->createCommand()->update("phone", array("primary" => 1), "user_id=:uid AND phone_digits=:p", array(":uid" => $userId, ":p" => $phone));

        return true;
    }

    /**
     * Упрощает телефон и возвращает из него только цифры. simplify($a) == simplify(simplify($a))
     * @param string $phone
     */
    public static function simplify($phone) {
        $res = array();
        $valid_char = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');

        for ($i = 0; $i < mb_strlen($phone); $i++) {
            if (in_array($phone[$i], $valid_char)) {
                $res[] = $phone[$i];
            }
        }
        
        //проверяем, если номер начинается не с 7, то добавляем её
        if ($res[0]!='7')
            array_unshift($res, '7');

        $res = implode($res);
        return $res;
    }
    
    public function delete($phoneId){
        Yii::app()->db->createCommand()->delete("phone", 'phone_id=:pid', array(":pid"=>$phoneId));
    }

    /**
     * Форматирует телефон под шаблон.
     * @param string $phone Номер телефона согласно одному из шаблонов regExp
     * @return string Пример: +7 (941) 123-4567
     */
    public static function format($phone) {
        $phone = Phone::simplify($phone);

        return "+" . mb_substr($phone, 0, 1) . " (" . mb_substr($phone, 1, 3) . ") " . mb_substr($phone, 4, 3). "-". mb_substr($phone, 7, 4);
    }

    public static function check($phone) {
        return preg_match(Phone::regExp(), $phone);
    }
    
    public static function exists($phone){
        $phone = Phone::simplify($phone);
        return Yii::app()->db->createCommand()->select("phone_id")
                ->from('phone')
                ->where( 'phone_digits=:p', array(":p"=>$phone))
                ->queryScalar();
    }

    public static function regExp($javaScript = false) {
        //регулярка принимает два формата:
        //1. +7 (999) 999-9999
        //2. 9999999999 (короткий, если маска ввода не сработала)
        //3. 79999999999 (если нужен 7)
        //4. +79999999999 (плюс тоже необязателен)
        $exp = "/(^\+7\s\(\d{3}\)\s\d{3}\-\d{4}$)|(^\+?7?[0-689]\d{9}$)/i";
        if (!$javaScript)
            $exp.="u";
        return $exp;
    }

}

?>
