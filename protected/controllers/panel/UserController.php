<?php

class UserController extends CPanelController {

    /**
     * Меняем параметры пользователя
     */
    public $errors = NULL;
    
    public function actionEdit() {
        $user = new User();
        $cPassword = new Password();
        $cCity = new City();
        $citiesAll = $cCity->getAll();
        $cities = array();
        foreach ($citiesAll as $val) {
            $cities[$val['city_id']] = $val['name'];
        }
        $this->render('editUser', array(
            'user' => $user->getByUser(Yii::app()->user->id),
            'id' => Yii::app()->user->id,
            'phones' => Phone::getByUser(Yii::app()->user->id),
            'dongle' => $cPassword->recoveryGet(),
            'cities' => $cities
        ));
    }

    /**
     * Обновляем параметры пользователя
     * @throws CHttpException
     */
    public function actionSave() {
        $user = new User('edit');
        $cEmail = new Email('edit'); 
        Helpers::required($_POST, array('user'));
        
        $user->attributes = $_POST['user'];
        
        if (isset($_POST['email']))
            $cEmail->attributes = $_POST['email'];
        
        if ($user->validate() && $cEmail->validate()) {
            //сохраняем изменения юзера
            $user->save();
            
            //сохраняем параметры рассылки
            $cEmail->updateSubscription();
            
            $this->redirect(array('panel/site/index'));
        } else {
            $this->errors = $user->getErrors() + $cEmail->getErrors();
            $this->actionEdit();
        }
    }

    /**
     * Выдаем форму редактирования пароля пользователя
     */
    public function actionEditPass($error = false) {
        $this->render('editPass', array('error' => $error));
    }

    public function actionSavePass() {
        $cPassword = new Password();
        Helpers::required($_POST, array('oldPass', 'newPass'));

        /* @todo добавить валидацию нового пароля на стороне сервера при смене пароля пользователем */
        if ($cPassword->check($_POST['oldPass'])) {
            $cUser = new User();
            $cUser->setPassword(Yii::app()->user->id, $_POST['newPass']);
            $this->redirect(array('panel/user/edit'));
        } else {
            return $this->actionEditPass(true);
        }
    }

    public function actionEditPassFast($dongle) {
        $cPassword = new Password();
        if (!$dongle || !$cPassword->recoveryCheck($dongle))
            throw new CHttpException(404, "Не найдено");

        $this->render('editPassFast', array('dongle' => $dongle));
    }

    public function actionSavePassFast($dongle) {
        $cPassword = new Password();
        Helpers::required($_POST, array('newPass'));

        /* @todo добавить валидацию нового пароля на стороне сервера при смене пароля пользователем */
        if ($cPassword->recoveryCheck($dongle)) {
            $cUser = new User();
            $cUser->setPassword(Yii::app()->user->id, $_POST['newPass']);
            $cPassword->recoveryRemove();
            $this->redirect(array('panel/user/edit'));
        } else {
            return $this->actionEditPassFast($dongle);
        }
    }

    //форма для добавления телефона
    public function actionAddPhone() {
        $user = new User();
        $this->render('addPhone');
    }

    //проверяет код, отправленный по СМС
    public function actionCheckCode() {
        $phone = $_REQUEST["user"]["phone"];
        if (!Phone::check($phone))
            throw new CHttpException(400, "Некорректный номер телефона");
        $p = new Phone();
        $p->requestConfirmation($phone, Yii::app()->user->getId());

        $this->render('checkCode', array(
            "phone" => $phone
        ));
    }

    public function actionInsertPhone() {
        $phone = $_POST["user"]["phone"];
        if (!Phone::check($phone))
            throw new CHttpException(400, "Некорректный номер телефона");
        $p = new Phone();
        if ($p->checkConfirmation($phone, $_POST["user"]["code"], Yii::app()->user->getId())) {
            Phone::add(Yii::app()->user->getId(), $phone);
            $this->redirect(array("panel/user/edit"));
        } else
            throw new CHttpException(400, "Неверно введен код подтверждения");
    }

    public function actionDeletePhone($phoneId, $confirmed = 0) {
        $cPhone = new Phone();
        if ($cPhone->count()<2) 
            throw new CHttpException(400,'Нельзя удалить все телефоны из аккаунта');
        
        $phone = $cPhone->getById($phoneId);
        if (!$phone || !$cPhone->checkOwnerById($phoneId))
            throw new CHttpException(400, 'Телефон не найден');
        $confirmed = (int) $confirmed;
        if (!$confirmed) {
            $this->render('deletePhone', array(
                "phone" => $phone
            ));
        } else {
            $cPhone->delete($phoneId);
            $this->redirect(array('panel/user/edit'));
        }
    }

}
