<?php

/**
 * класс управления страницами категории
 */
class Flypage extends CFormModel{
	static function getList(){
		return array(
			'default'=>'default (по умолчанию)',
			'sell_auto'=>'sell_auto (для продажи авто)',
		);
	}
}
