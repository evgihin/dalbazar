<?php

class Sms extends CFormModel {

    /**
     * Регистрирует полученное сообщение в базе данных
     * @param type $service Шлюз, через который принято сообщение
     * @param type $message Текст сообщения
     * @param type $number Номер, НА который было отправлено сообщение
     * @param type $operatorId Ид оператора отправителя в международном формате
     * @param type $operator Имя оператора отправителя
     * @param type $phone Номер телефона, с которого было отправлено сообщение
     * @param type $additional Дополнительная информация по сообщению
     */
    static function get($service, $message, $number, $operatorId, $operator, $phone, $additional) {
        Yii::app()->db->createCommand()
                ->insert("recieved_sms", array(
                    'service' => $service,
                    'message' => $message,
                    'number' => $number,
                    'operator_id' => $operatorId,
                    'operator' => $operator,
                    'phone' => $phone,
                    'additional' => serialize($additional),
                    'time' => time(),
        ));
        return Yii::app()->db->lastInsertID;
    }

    static function send($number, $text) {
        return Sms::sendProfiSend($number, $text);
    }

    static function sendSmsRent($number, $text) {
        $number = Phone::simplify($number);

        if (!$curl = curl_init("http://smsrent.ru/smsgateway/send.php"))
            return false;

        // Передача данных осуществляется методом POST
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // Задаем POST-данные
        $data = array(
            "sender" => "dalbazar.ru",
            "to" => $number,
            "msg" => $text,
            "id" => "15122",
            "skey" => "as01jd892_23kaa",
            "in_enc" => "UTF-8"
        );
        $data["sign"] = md5($data['sender'] . $data['to'] . $data['msg'] . $data['id'] . md5($data['skey']));

        $data2 = "";
        foreach ($data as $key => $value) {
            $data2.=$key . "=" . $value . "&";
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data2);

        curl_setopt($curl, CURLOPT_TIMEOUT, 5); // times out after 4s 

        if (!Yii::app()->params['smsSimulationMode'])
            $return = curl_exec($curl);
        else
            $return = "9999;9999";

        // Закрываем CURL соединение
        curl_close($curl);

        //регистрируем отправку СМС в базе
        Yii::app()->db->createCommand()->insert("sended_sms", array(
            'phone' => $number,
            'text' => $text,
            'response' => $return,
            'time' => time()
        ));

        return $return;
    }

    static function sendProfiSend($number, $text) {
        $number = Phone::simplify($number);
        /*
         * Использован код php-класса SmsServiceApi.class.php для работы с API.
         * Исходный код доступен на странице http://cabinet.profisend.ru/help/api/sample
         */

        $Api = new ProfiSend("13269", "30939ca523edb7a4dcca12fbd66fb5fd");

        // параметры
        $api_params = array(
            'pid' => "15122",
            'sender' => 'dalbazar.ru',
            'to' => $number,
            'text' => $text
        );

        // отправка
        if (!Yii::app()->params['smsSimulationMode'])
            $result = $Api->send('delivery.sendSms', $api_params);
        else 
            $result = "9999;9999";
        
        if (is_array($result)) $result = serialize ($result);
        
        //регистрируем отправку СМС в базе
        Yii::app()->db->createCommand()->insert("sended_sms", array(
            'phone' => $number,
            'text' => $text,
            'response' => $result,
            'time' => time()
        ));

        return $result;
    }

}
