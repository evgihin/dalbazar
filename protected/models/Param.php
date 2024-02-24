<?php

/**
 * Класс управления параметрами фильтров
 */
class Param extends CExtFormModel {

	var $filter_id,$name,$pos=9999,$removed=0;
        
        var $order = "pos ASC";

	function rules(){
		return array(
			array('filter_id, pos','numerical', 'integerOnly' => true,'on'=>'add'),
			array('name, filter_id','required', 'on'=>'add'),
		);
	}

	function insert(){
		Yii::app()->db->createCommand()
				->insert('filter_param', $this->getAttributes(array('filter_id','name','pos')));
		return Yii::app()->db->lastInsertID;
	}

	function getByParam($paramId, $order = '') {
		$arr = $paramId;
		if (!is_array($paramId)) {
			$arr = array($paramId);
		}
		$command = Yii::app()->db->createCommand()
				->select('*')
				->from('filter_param')
				->where(array('IN', 'filter_param_id', $arr));
		if ($order)
			$command->order(array($order, 'pos ASC', 'name ASC'));
		else
			$command->order(array('pos ASC', 'name ASC'));
		$res = $command->query();
		if (is_array($paramId))
			return $res->readAll(); else
			return $res->read();
	}

	function getByFilter($filterId) {
		if (!is_array($filterId)) {
			$filterId = array($filterId);
		}
		return Yii::app()->db->createCommand()
						->select('filter_param.*')
						->from('filter_param')
						->where(array('IN', 'filter_param.filter_id', $filterId))
						->order(array('filter_param.pos ASC', 'filter_param.name ASC'))
						->queryAll();
	}

	function getByFilterOnlyDepend($filterId, $dependIds) {
		if (!is_array($filterId)) {
			$filterId = array($filterId);
		}
		if (!is_array($dependIds) || !$dependIds)
			throw new CException('Параметр $dependIds должен быть непустым массивом');

		return Yii::app()->db->createCommand()
						->select('filter_param.*')
						->from('filter_param')
						->join('filter_depending', 'filter_depending.filter_param_id = filter_param.filter_param_id')
						->where(array('AND', array('IN', 'filter_param.filter_id', $filterId), array('IN', 'filter_depending.filter_depending_param_id', $dependIds)))
						->order(array('filter_param.pos ASC', 'filter_param.name ASC'))
						->queryAll();
	}

	/**
	 * Получает значения фильтров для определенного объявления
	 * @param mixed $advertId Может быть либо ИД-ом одного объявления, либо массивом ИД-ов
	 * @param string $type тип получаемых значений. пока доступны s либо i.
	 * @return array массив параметров (НЕ АССОЦИАТИВНЫЙ)
	 */
	function getValues($advertId, $type = NULL) {
		if (is_int($advertId)) {
			$advertId = array($advertId);
		}
		$command = Yii::app()->db->createCommand()
				->select()
				->from('advert_filter')
				->where(array('IN', 'advert_id', $advertId))
				->order('advert_id ASC');
		if ($type)
			$command->where_and('type=:type', array(':type' => $type));
		return $command->queryAll();
	}
        
        function getValuesFull($advertId){
            if (!is_array($advertId))
                $advertId = array($advertId);
            return $this->createCommand()
                    ->select("advert_filter.*, filter.type, filter.piece, filter.removed, filter.name AS filter_name, filter_param.name AS filter_param_name")
                    ->from("advert_filter")
                    ->join("filter", "advert_filter.filter_id=filter.filter_id")
                    ->leftJoin("filter_param", "advert_filter.filter_param_id=filter_param.filter_param_id")
                    ->where("filter.removed=0")
                    ->where_and(array("IN","advert_filter.advert_id",$advertId))
                    ->order("filter.pos ASC")
                    ->queryAll();
        }

	//получает список зависимых параметров на основе установленных фильтров
	public function getParamByDependParam($depending_param_id, $filter_id = Null) {
		$arr = $depending_param_id;
		if (!is_array($depending_param_id))
			$arr = array($depending_param_id);
		$comm = Yii::app()->db->createCommand()
						->select('filter_param.*')
						->from('filter_param')
						->join('filter_depending', 'filter_depending.filter_param_id=filter_param.filter_param_id')
						->where(array('AND', array('IN', 'filter_depending.filter_depending_param_id', $arr), 'filter_param.removed=0'))
						->order('pos ASC', 'name ASC');
                if (!is_null($filter_id)){
                    if (!is_array($filter_id))
			$arr = array($filter_id);
                    $comm->where_and(array("IN","filter_param.filter_id",$arr));
                }
		return $comm->queryAll();
	}

}
