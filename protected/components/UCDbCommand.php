<?php
//класс расширяет стандартный, дополняя две необходимые функции, позволяющие многократно использовать WHERE
/**
 * @method UCDbCommand select($columns='*', $option='')
 * @method UCDbCommand reset()
 * @method UCDbCommand crossJoin($table)
 * @method UCDbCommand from($tables)
 * @method UCDbConnection getConnection()
 * @method UCDbCommand group($columns)
 * @method UCDbCommand having($conditions, $params=array())
 * @method UCDbCommand join($table, $conditions, $params=array())
 * @method UCDbCommand leftJoin($table, $conditions, $params=array())
 * @method UCDbCommand limit($limit, $offset=null)
 * @method UCDbCommand naturalJoin($table)
 * @method UCDbCommand offset($offset)
 * @method UCDbCommand order($columns)
 * @method UCDbCommand rightJoin($table, $conditions, $params=array())
 * @method UCDbCommand selectDistinct($columns='*')
 * @method UCDbCommand where($conditions, $params=array())
 */
class UCDbCommand extends CDbCommand {

  /**
   * Добавляет к предыдущим WHERE новые условия через "И" оператор
   * @param type $conditions параметры, задаваемые так же как и в WHERE
   * @param type $params переменные, синтаксис тот же что и у WHERE
   * @return \UCDbCommand
   */
  public function where_and($conditions, $params = array()) {
    if (isset($this->_query['where']) && $this->_query['where']) {
      $this->_query['where'] = $this->processConditions(array('AND', $this->_query['where'], $conditions));
      foreach ($params as $name => $value)
        $this->params[$name] = $value;
    } else $this->where($conditions, $params);
    return $this;
  }

  /**
   * Добавляет к предыдущим WHERE новые условия через "ИЛИ" оператор
   * @param type $conditions параметры, задаваемые так же как и в WHERE
   * @param type $params переменные, синтаксис тот же что и у WHERE
   * @return \UCDbCommand
   */
  public function where_or($conditions, $params = array()){
    if (isset($this->_query['where']) && $this->_query['where']) {
      $this->_query['where'] = $this->processConditions(array('OR', $this->_query['where'], $conditions));
      foreach ($params as $name => $value)
        $this->params[$name] = $value;
    } else $this->where($conditions, $params);
    return $this;
  }

}

