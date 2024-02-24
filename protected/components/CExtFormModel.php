<?php
/** 
 * Расширенная форма
 * Имеет по умолчанию limit и offset параметры
 * и встроенный конструктор 
 */
class CExtFormModel extends CFormModel{
    
    var $limit = NULL;
    var $offset = NULL;
    var $order = '';
    
    /**
     * Создает команду-заготовку для запроса к БД
     * Автоматически добавляет к команде limit и offset параметры
     * @return UCDbCommand Команда, которая может быть обращена к БД
     */
    protected function createCommand($q=NULL) {
        $comm = Yii::app()->db->createCommand($q);

        if (is_null($q)){
            if (!is_null($this->limit)) {
                $comm->limit($this->limit, $this->offset);
            }
        }

        return $comm;
    }
    
        /**
     * Модифицирует запрос на попутный расчет количества запросов. После этой функции нельзя больше модифицировать SELECT
     * @param UCDbCommand $comm
     */
    protected function calculateCount(&$comm) {
        $select = str_replace('`', '', $comm->select); //двига автоматически ставит кавычки, убираем их чтобы не ставила много раз
        $comm->select($select, "SQL_CALC_FOUND_ROWS");
    }
    
    public function get($table,$where=NULL,$params=array(),$count=false){
         $comm = $this->createCommand()->select('*')->from($table);
         if (!is_null($where))
            $comm->where($where, $params);
         if ($this->order)
             $comm->order($this->order);
         if ($count)
             $this->calculateCount($comm);
         return $comm->queryAll();
    }
    
    /**
     * Считает количество элементов в таблице или в выборке
     * @param string $table Если пусто то высчитывает количество результатов, возвращенных предыдущим запросом (за исключением LIMIT и OFFSET)
     * @return int Количество элементов запроса либо таблицы
     */
    public function count($table=''){
        if ($table)
            return $this->createCommand()->select('COUNT(*)')->from($table)->queryScalar();
        else
            return $this->createCommand("SELECT FOUND_ROWS();")->queryScalar();
    }
}