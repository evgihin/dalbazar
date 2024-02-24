<?php

class vLoginBusy extends CArrayValidator {

    public $defaultMessage = "Логин уже занят";

    public function validateValue($login) {
        $cUser = new User();
        return !$cUser->checkLogin($login) || Yii::app()->user->getState('login')==$login;
    }

}

?>
