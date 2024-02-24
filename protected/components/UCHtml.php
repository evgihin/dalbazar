<?php

class UCHtml extends CHtml {

	static function activeDropDownList($model, $attribute, $data, $htmlOptions = array()) {
		$hash = md5($attribute);
		$val = Yii::app()->user->getState($hash);
		if (property_exists($model, $attribute) && $val && !$model->$attribute) {
			$model->$attribute = $val;
		}
		return parent::activeDropDownList($model, $attribute, $data, $htmlOptions);
	}

	static function activeDropDownListSaveDefault($attribute, $value) {
		$hash = md5($attribute);
		Yii::app()->user->setState($hash, $value);
	}

	static function dropDownList($name, $select, $data, $htmlOptions = array()) {
		$hash = md5($name);
		$val = Yii::app()->user->getState($hash);
		if ($val && !$select) {
			$select = $val;
		}
		return parent::dropDownList($name, $select, $data, $htmlOptions);
	}

}

