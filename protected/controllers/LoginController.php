<?php

class LoginController extends CFrontEndController {

    public $left;

    /**
     * Displays the login page
     */
    public function actionIndex() {
        if (Yii::app()->user->isGuest) {
            $model = new LoginForm('login');

            // collect user input data
            if (isset($_POST['LoginForm'])) {
                $model->attributes = $_POST['LoginForm'];
                // validate user input and redirect to the previous page if valid
                $redirect = '';
                if (Yii::app()->request->isAjaxRequest) {
                    if ($model->validate() && $model->login())
                        $redirect = Yii::app()->user->returnUrl;
                    echo CJSON::encode(array('error' => implode('<br>', Helpers::getVarArray($model->getErrors())), 'redirect' => $redirect));
                } else {
                    if ($model->validate() && $model->login())
                        $this->redirect ( Yii::app()->user->returnUrl );
                    else {
                        $this->render('login', array('model' => $model));
                    }
                }
            } else {
                $this->render('login', array('model' => $model));
            }
        } else
            $this->redirect(Yii::app()->homeUrl);
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout() {
        Yii::app()->user->logout(false);
        $this->redirect(Yii::app()->homeUrl);
    }

    public function actionRegister() {
        $model = new LoginForm;
        $model->scenario = 'register';
        $redirect = '';

        $model->attributes = $_POST['RegisterForm'];
        if ($model->validate() && $model->register() && $model->login()) {
            $redirect = Yii::app()->user->returnUrl;
        }
        echo CJSON::encode(array('error' => implode('<br>', Helpers::getVarArray($model->getErrors())), 'redirect' => $redirect));
    }

    /**
     * Вход на сайт по почтовому ящику
     */
    public function actionEmail() {
        if (!Helpers::required($_POST, array("login", "password"), false)) {
            echo CJSON::encode(array("error" => "Данные указаны некорректно"));
            return;
        }
        $cvEmail = new CEmailValidator();

        if ($cvEmail->validateValue($_POST['login'])) {
            $cUser = new User();
            $user = $cUser->getByEmail($_POST['login']);
            if ($user) {
                if ($user["pass"] === Helpers::hash($_POST['password'])) {
                    $cUser->login($user['user_id']);
                    echo CJSON::encode(array("redirect" => Yii::app()->user->returnUrl));
                    return;
                }
            }
        }

        echo CJSON::encode(array("error" => "E-mail или пароль указаны неверно"));
        return;
    }

    /**
     * Вход по номеру телефона
     */
    public function actionPhone() {
        if (!Helpers::required($_POST, array("login", "password"), false)) {
            echo CJSON::encode(array("error" => "Данные указаны некорректно"));
            return;
        }

        Yii::import("application.validators.vPhone");
        $cvPhone = new vPhone();

        if ($cvPhone->validateValue($_POST['login'])) {
            $cUser = new User();
            $user = $cUser->getByPhone($_POST['login']);
            if ($user) {
                //проверяем пароль
                //сначала сверяем с базой
                if ($user["pass"] === Helpers::hash($_POST['password'])) {
                    $cUser->login($user['user_id']);
                    echo CJSON::encode(array("redirect" => Yii::app()->user->returnUrl));
                    return;
                } else {
                    //если не подошел, сверяем с подтверждением телефона
                    $cPhone = new Phone();
                    if ($cPhone->checkConfirmation($_POST['login'], $_POST['password'], NULL, Yii::app()->session->sessionID)) {
                        //$cUser->setPassword($user["user_id"], $_POST['password']);
                        $cPassword = new Password();

                        $cUser->login($user['user_id']);

                        $cPassword->recoveryGenerate();
                        echo CJSON::encode(array("redirect" => Yii::app()->user->returnUrl));
                        return;
                    }
                }
            }
        }

        echo CJSON::encode(array("error" => "Номер телефона или пароль указаны неверно"));
        return;
    }

    /**
     * Вход по имени пользователя
     */
    public function actionLogin() {
        if (!Helpers::required($_POST, array("login", "password"), false)) {
            echo CJSON::encode(array("error" => "Данные указаны некорректно"));
            return;
        }

        $cUser = new User();
        $user = $cUser->getByLogin($_POST['login']);

        if ($user['pass'] === Helpers::hash($_POST['password'])) {
            $cUser->login($user['user_id']);
            echo CJSON::encode(array("redirect" => Yii::app()->user->returnUrl));
            return;
        }

        echo CJSON::encode(array("error" => "Логин или пароль указаны неверно"));
        return;
    }

    public function actionRecoveryEmail() {
        if (!Helpers::required($_POST, array("login"), false)) {
            echo CJSON::encode(array("error" => "Данные указаны некорректно"));
            return;
        }

        $cvEmail = new CEmailValidator();

        if ($cvEmail->validateValue($_POST['login'])) {
            $cUser = new User();
            $user = $cUser->getByEmail($_POST['login']);
            if ($user) {
                //генерим новый пасс
                $cPass = new Password();
                $pass = $cPass->generateEmail();
                /* @var $cEmail Email */
                $cEmail = Yii::app()->email;
                $cEmail->to = $_POST['login'];
                $cEmail->view = "recoveryPassword";
                if ($cEmail->send(array("password" => $pass))) {
                    $cUser->setPassword($user['user_id'], $pass);
                    echo CJSON::encode(array("error" => "На указанный ящик отправлено письмо с новым паролем"));
                    return;
                } else
                    $err = "Ошибка сервера, повторите запрос позже";
            } else
                $err = "Пользователь с таким e-mail не обнаружен";
        } else
            $err = "E-mail указан неверно";

        echo CJSON::encode(array("error" => $err));
        return;
    }

    public function actionRecoveryPhone() {
        if (!Helpers::required($_POST, array("login"), false)) {
            echo CJSON::encode(array("error" => "Данные указаны некорректно"));
            return;
        }

        Yii::import("application.validators.vPhone");
        $cvPhone = new vPhone();

        if ($cvPhone->validateValue($_POST['login'])) {
            $cUser = new User();
            $user = $cUser->getByPhone($_POST['login']);
            if ($user) {
                $cPhone = new Phone();
                if ($cPhone->requestConfirmation($_POST['login'], NULL, Yii::app()->session->sessionID)) {
                    echo CJSON::encode(array("error" => "На указанный телефон отправлен код подтверждения. Введите его в поле \"пароль\""));
                    return;
                } else
                    $err = "Вы исчерпали попытки восстановления по СМС. Попробуйте позже.";
            } else
                $err = "Пользователь с таким телефоном не обнаружен";
        } else
            $err = "Телефон указан неверно";

        echo CJSON::encode(array("error" => $err));
        return;
    }

}
