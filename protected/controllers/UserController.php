<?php

class UserController extends CFrontEndController {

    public function actionMain() {
        $this->render('main');
    }

    public function actionCheck($login) {
        //если встретился свой логин, не проверяем
        if (Yii::app()->user->getState('login')==$login){
            echo "true";
            return;
        }
        $cUser = new User();
        if (!$cUser->checkLogin($login)) {
            echo "true";
            return;
        }
        
        echo "false";
        return;
    }

}
