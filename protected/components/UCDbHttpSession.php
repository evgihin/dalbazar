<?php

/**
 * UCDbHttpSession class
 */
class UCDbHttpSession extends CDbHttpSession {

  /**
   * Обновляет ИД пользователя для текущей сессии. Если пользователь не вышел под другой сессией - то обновляем его старую сессию, а текущую удаляем и перекидываем всю статистику
   * @param type $userId ИД пользователя

  public function updateUserId($userId) {
    $oldId = session_id();
    // Если сессия не запущена - нечего менять
    if (empty($oldId))
      return;

    $db = $this->getDbConnection();

    //проверяем, залогинен ли пользователь с другого браузера (или с другой сессии) и перекидываем ту сессию на него
    $sessRow = $db->createCommand()
            ->select('id,data')
            ->from($this->sessionTableName)
            ->where('user_id=:user_id', array(':user_id' => $userId))
            ->queryRow();
    if ($sessRow !== false && $sessRow['id'] != $oldId) {
      $newId = $sessRow['id'];

      //закрываем сессию для чистоты махинаций с данными
      $this->close();

      //обновляем user agent и data новой сессии
      $db->createCommand()->update($this->sessionTableName, array('user_agent' => Yii::app()->request->getUserAgent()), 'id=:sid', array(':sid' => $newId));

      //перекидываем все данные таблиц со старой сессии на новую
      foreach (Yii::app()->params['updateSIDTables'] as $val) {
        $db->createCommand()->update($val, array('sid' => $newId), 'sid=:sid', array(':sid' => $oldId));
      }

      //обновляем ИД сессии в куках (или в строке браузера)
      session_id($newId);

      //удаляем старую сессию
      $this->destroySession($oldId);

      $this->open();
    } elseif ($sessRow != $oldId) {
      $db->createCommand()->update($this->sessionTableName, array('user_id' => $userId), 'id=:id AND user_agent=:user_agent', array(':id' => $oldId, ':user_agent' => Yii::app()->request->getUserAgent()));
    }
  }

  public function deleteUserId() {
    $this->getDbConnection()->createCommand()->update($this->sessionTableName, array('user_id' => '0'), 'id=:id AND user_agent=:user_agent', array(':id' => session_id(), ':user_agent' => Yii::app()->request->getUserAgent()));
  }

  /**
   * @return integer the number of seconds after which data will be seen as 'garbage' and cleaned up, defaults to 1440 seconds.
   */
  public function getTimeout() {
    if ($this->timeout)
      return $this->timeout; else
      return (int) ini_get('session.gc_maxlifetime');
  }

  /**
   * @param integer $value the number of seconds after which data will be seen as 'garbage' and cleaned up
   */
  public function setTimeout($value) {
    $this->timeout = (int) $value;
  }

  /**
   * Обновляет ИД сессии и заменяет ИД в базе данных
   */
  public function regenerateID($deleteOldSession = false) {
    $oldID = session_id();

    // Если сессия не запущена - нечего менять
    if (empty($oldID))
      return;

    session_regenerate_id($deleteOldSession);
    $newID = session_id();
    $db = $this->getDbConnection();

    $row = $db->createCommand()
            ->select()
            ->from($this->sessionTableName)
            ->where('id=:id AND user_agent=:user_agent', array(':id' => $oldID, ':user_agent' => Yii::app()->request->getUserAgent()))
            ->queryRow();
    if ($row !== false) {
      $db->createCommand()->update($this->sessionTableName, array(
          'id' => $newID
              ), 'id=:oldID AND user_agent=:user_agent', array(':oldID' => $oldID, ':user_agent' => Yii::app()->request->getUserAgent()));
    } else {
      // shouldn't reach here normally
      $db->createCommand()->insert($this->sessionTableName, array(
          'id' => $newID,
          'expire' => time() + $this->getTimeout(),
          'user_agent' => Yii::app()->request->getUserAgent(),
          'user_id' => 0
      ));
    }
  }

  /**
   * Создает таблицу в БД для сессии
   * @param CDbConnection $db подключение к БД
   * @param string $tableName Имя таблицы, которое будет создано
   */
  protected function createSessionTable($db, $tableName) {
    $driver = $db->getDriverName();
    $db->createCommand()->createTable($tableName, array(
        'id' => 'CHAR(32) PRIMARY KEY',
        'expire' => 'integer',
        'user_agent' => 'text',
        'data' => 'longtext',
        'user_id' => 'integer'
    ));
  }

  /**
   * Считывает сессию
   * Не вызывайте этот метод напрямую
   * @param string $id session ID
   * @return string the session data
   */
  public function readSession($id) {
    $data = $this->getDbConnection()->createCommand()
            ->select('data')
            ->from($this->sessionTableName)
            ->where('expire>:expire AND id=:id AND user_agent=:user_agent', array(':expire' => time(), ':id' => $id, ':user_agent' => Yii::app()->request->getUserAgent()))
            ->queryScalar();
    return $data === false ? '' : $data;
  }

  /**
   * Session write handler.
   * Do not call this method directly.
   * @param string $id session ID
   * @param string $data session data
   * @return boolean whether session write is successful
   */
  public function writeSession($id, $data) {
    // exception must be caught in session write handler
    // http://us.php.net/manual/en/function.session-set-save-handler.php
    try {
      $expire = time() + $this->getTimeout();
      $db = $this->getDbConnection();
      if ($db
                      ->createCommand()
                      ->select('id')
                      ->from($this->sessionTableName)
                      ->where('id=:id AND user_agent=:user_agent', array(':id' => $id, ':user_agent' => Yii::app()->request->getUserAgent()))
                      ->queryScalar()
              === false) {
        $db->createCommand()->insert($this->sessionTableName, array(
            'id' => $id,
            'data' => $data,
            'expire' => $expire,
            'user_agent' => Yii::app()->request->getUserAgent(),
           // 'user_id' => 0,
        ));
      }
      else
        $db->createCommand()->update($this->sessionTableName, array(
            'data' => $data,
            'expire' => $expire
                ), 'id=:id', array(':id' => $id));
    } catch (Exception $e) {
      if (YII_DEBUG)
        echo $e->getMessage();
      // it is too late to log an error message here
      return false;
    }
    return true;
  }

  /**
   * Session destroy handler.
   * Do not call this method directly.
   * @param string $id session ID
   * @return boolean whether session is destroyed successfully
   */
  public function destroySession($id) {
    $this->getDbConnection()->createCommand()
            ->delete($this->sessionTableName, 'id=:id AND user_agent=:user_agent', array(':id' => $id, ':user_agent' => Yii::app()->request->getUserAgent()));
    return true;
  }

}
