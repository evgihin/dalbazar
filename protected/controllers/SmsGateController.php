<?php

class SmsGateController extends CFrontEndController {

    public $left;
    protected $_errors;

    public function actionSmsRent() {
        $security='off';
        if (Helpers::required($_GET, array(
                    'msg_trans',
                    'msg',
                    'num',
                    'operator_id',
                    'operator',
                    'user_id',
                    'price',
                    'cost',
                    'skey'
                ),false) && (($security=='on' &&
                md5('falaqkedjmw' . $_GET['msg_trans'] . $_GET['msg']
                        . $_GET['num'] . $_GET['operator_id']
                        . $_GET['operator'] . $_GET['user_id'] .
                        $_GET['price'] . $_GET['cost']) == $_GET['skey']
                ) || $security=='off' )
        ) {
            Sms::get("smsrent", $_GET['msg'], $_GET['num'], $_GET['operator_id'], $_GET['operator'], $_GET['user_id'], $_GET);
            $hasError = false;
            $cAdvert = new Advert();
            //проверяем текст сообщения и номер объявления
            if (preg_match("/^regbazar\s?(\d+).*$/ui", $_GET['msg'], $res)) {
                //получаем номер объявления
                $advert_id = $res[1];
                if ($cAdvert->checkAvailability($advert_id, true)) {

                    switch ($_GET['num']) {
                        case '4345': //поднять объявление
                            $cAdvert->pickUp($advert_id);
                            break;
                        case '2320': //сделать жирным и поднять
                            $cAdvert->setBold($advert_id);
                            $cAdvert->pickUp($advert_id);
                            break;
                        case '8385': //опубликовать на главной на сутки и поднять
                            $cAdvert->publishToMain($advert_id);
                            $cAdvert->pickUp($advert_id);
                            break;
                        default:
                            throw new CHttpException(400,"Неизвестный номер шлюза отправки");
                            break;
                    }
                } else 
                    throw new CHttpException(400,"Неизвестный ИД объявления");
            } else 
                throw new CHttpException(400,"Текст сообщения не распознан системой");
            
        } else echo "Запрос неверный. Повторите.";
    }

}
