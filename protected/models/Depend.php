<?php

class Depend extends CFormModel{
	var $filter_param_id, $filter_depending_param_id;

	function rules(){
		return array(
			array('filter_param_id, filter_depending_param_id', 'numerical', 'integerOnly' => true,'on'=>'add'),
		);
	}

	function insert(){
		Yii::app()->db->createCommand()
				->insert('filter_depending', $this->getAttributes(array('filter_param_id', 'filter_depending_param_id')));
		return Yii::app()->db->lastInsertID;
	}
}

