<?php

class PhoneController extends CFrontEndController {

    function __construct($id, $module = null) {
        parent::__construct($id, $module);
        define("STATE_SENDED", 2);
        define("STATE_ERROR", 1);
        define("STATE_CONFIRMED", 4);
    }

    /**
     * Отправляет пользователю СМС с кодом подтверждения
     * В качестве результата работы возвращает массив следующего формата:
     * int remain - сколько осталось попыток доставки СМС пользователю
     * string state - Описание состояния запроса
     * int stateCode - Код состояния запроса
     */
    function _check() {

        $result = array(
            "remain" => 0, //сколько осталось СМС у пользователя
            "text" => "", //текст состояния
            "state" => 0, //имя состояния (sended, rejected|resend, rejected|end, confirmed, notRemainMessage, error)
        );
        $phone = new Phone();

        if (!isset($_REQUEST['phone']) || !Phone::check($_REQUEST['phone'])) {
            $result["state"] |= STATE_ERROR;
            $result["text"] = "Телефон не указан либо указан неверно";
            return $result;
        }
        $num = $_REQUEST['phone'];

        if (!isset($_REQUEST['code'])) {
            //если не указан код подтверждения, отправляем его
            if ($phone->requestConfirmation($num, NULL, Yii::app()->session->sessionID)) {
                $result["remain"] = $phone->reminingConfirmations($num, NULL, Yii::app()->session->sessionID) + 1;
                $result["text"] = "На номер " . $num . " было отправлено СМС с кодом подтверждения.
                Введите полученный код в поле ниже.";
                $result["state"] &= STATE_SENDED;
            } else {
                $result["remain"] = $phone->reminingConfirmations($num, NULL, Yii::app()->session->sessionID);
                $result["text"] = "Вы исчерпали доступные попытки. Попробуйте активировать телефон позже либо указать другой номер телефона.";
                $result["state"] &= STATE_ERROR;
            }
        } else {
            //если он указан, проверяем
            if (!is_numeric($_REQUEST['code'])) {
                //код не прошел проверку
                $result["text"] = "Код подтверждения не указан либо указан неверно";
                $result["state"] |= STATE_ERROR;
            } else {
                $code = $_REQUEST['code'];

                if ($phone->checkConfirmation($num, $code, NULL, Yii::app()->session->sessionID)) {
                    $result["text"] = "Ваш телефон подтвержден. Для упрощения авторизации установите пароль в личном кабинете.";
                    $result["state"] |= STATE_CONFIRMED;
                } else {

                    if ($phone->requestConfirmation($num, NULL, Yii::app()->session->sessionID)) {
                        $result["remain"] = $phone->reminingConfirmations($num, NULL, Yii::app()->session->sessionID) + 1;
                        $result["text"] = "СМС код неверен. Вам отправлено новое сообщение с кодом.";
                        $result["state"] |= STATE_ERROR | STATE_SENDED;
                    } else {
                        $result["remain"] = $phone->reminingConfirmations($num, NULL, Yii::app()->session->sessionID);
                        $result["text"] = "Вы исчерпали лимит отправленных СМС на сегодня. Повторите попытку позже либо укажите другой номер телефона.";
                        $result["state"] |= STATE_ERROR;
                    }
                }
            }
        }

        return $result;
    }

    function actionCheckAndLogin() {
        $res = $this->_check();
        if ($res['state'] & STATE_CONFIRMED) {
            //заходим под соответствующим юзером
            $cUser = new User();
            if ($user = $cUser->getByPhone($num)) {
                //если пользователь уже есть в базе, логинимся под ним
                $cUser->login($user['user_id']);
            } else {
                //иначе создаем нового пользователя
                $id = $cUser->create();
                $cUser->login($id);
                //и привязываем к нему созданный телефон
                $phone->add($id, $num, true);
            }
        }
        echo CJSON::encode($res);
    }

}

?>
