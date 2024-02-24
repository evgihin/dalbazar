<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity {

  /**
   * Authenticates a user.
   * The example implementation makes sure if the username and password
   * are both 'demo'.
   * In practical applications, this should be changed to authenticate
   * against some persistent user identity storage (e.g. database).
   * @return boolean whether authentication succeeds.
   */
  public $userdata;
  public $_id;

  public function authenticate() {
    $result = Yii::app()->db->createCommand()
            ->select('*')
            ->from('user')
            ->where(array('AND', 'login=:login', 'pass=:pass'), array(':login' => $this->username, ':pass' => Helpers::hash($this->password)))
            ->queryRow();
    if ($result) {
      $this->setPersistentStates($result);
      $this->_id = $result['user_id'];
      $this->username = $result['login'];
      $this->errorCode = self::ERROR_NONE;
    } else
      $this->errorCode = self::ERROR_PASSWORD_INVALID;
    return !$this->errorCode;
  }

  public function getId() {
    return $this->_id;
  }

}