<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel {

  public $username;
  public $password;
  public $phone;

  /**
   * Declares the validation rules.
   * The rules state that username and password are required,
   * and password needs to be authenticated.
   */
  public function rules() {
    $regPhone = "/^\+7\s\(\d{3}\)\s\d{3}\-\d{4}$/iu"; //проверка введенного телефона. на клиенте он вводится маской, поэтому проверка только по маске
    return array(
        // username and password are required
        array('username, password', 'match', 'allowEmpty' => false, 'pattern' => '~^[a-zA-Z0-9\_\=\+\-\*\^\&\?\.\,\!\@\#\%\$]{4,20}$~', 'on' => 'login,register', 'message' => 'Логин либо пароль указан неверно'),
        array('phone', 'match', 'allowEmpty' => false, 'pattern' => $regPhone, 'on' => 'register', 'message' => 'Телефон указан неверно'),
    );
  }

  /**
   * Logs in the user using the given username and password in the model.
   * @return boolean whether login is successful
   */
  public function login() {
    $identity = new UserIdentity($this->username, $this->password);
    if ($identity->authenticate()) {
      Yii::app()->user->login($identity);
      return true;
    } else {
      $this->addError('password', 'Такого пользователя не существует, либо пароль неверен');
      return false;
    }
  }

  public function register() {
    if (Yii::app()->db->createCommand()->select()->from('user')->where('login=:l OR phone1=:p', array(':l' => $this->username, ':p' => $this->phone))->queryScalar()) {
      $this->addError('username', 'Такой пользователь уже существует либо ваш телефон указан кем-то другим');
      return false;
    }
    Yii::app()->db->createCommand()
            ->insert('user', array(
                'login' => $this->username,
                'pass' => Helpers::hash($this->password),
                'phone1' => $this->phone
            ));
    return Yii::app()->db->lastInsertID;
  }

}
