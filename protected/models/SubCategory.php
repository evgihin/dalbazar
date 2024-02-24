<?php

class SubCategory extends CFormModel {

	public $alias, $name, $category_parent_id, $flypage;

	public function attributeLabels() {
		return array(
			'alias' => 'Алиас',
			'name' => 'Имя категории',
			'flypage' => 'Шаблон категории',
			'template' => "Шаблон объявления",
			'category_parent_id' => "Родительская категория",
		);
	}

	public function rules() {
		$regex = "/^[a-zA-Z0-9\-\_\.]+$/";
		$regex2 = "/^[a-zA-Z0-9а-яА-Я\s\-\_\.\/\\\(\)]+$/ui";

		return array(
			array('alias, flypage, name, category_parent_id', "required", 'on' => 'edit, add'),
			array('alias, flypage', "match", 'pattern' => $regex, 'on' => 'edit, add'),
			array('category_parent_id', 'match', 'pattern' => '/^[0-9]+$/', 'on' => 'edit, add'),
			array('category_parent_id', '_hasCategory', 'on' => 'edit, add'),
			array('name', "match", 'pattern' => $regex2, 'on' => 'edit, add'),
			array('flypage', "_inArray", 'array' => array_keys(Flypage::getList()), 'on' => 'edit, add'),
		);
	}

	public function _inArray($attr, $params) {
		if (!in_array($this->$attr, $params['array']))
			$this->addError($attr, 'Параметр должен быть одним из перечисленных');
	}

	public function _hasCategory($attr, $params) {
		$category = new Category();
		$cat = $category->getByCategory($this->$attr);
		if (!$cat || $cat['category_parent_id'] != 0)
			$this->addError($attr, 'Ид категории указан неверно');
	}

	public function update($categoryId) {
		UCHtml::activeDropDownListSaveDefault('category_parent_id', $this->category_parent_id);
		Yii::app()->db->createCommand()
				->update('category', $this->getAttributes(array(
							'alias',
							'name',
							'flypage',
							'category_parent_id',
						)), 'category_id=:cid', array(':cid' => $categoryId));
	}

	public function insert() {
		UCHtml::activeDropDownListSaveDefault('category_parent_id', $this->category_parent_id);
		Yii::app()->db->createCommand()
				->insert('category', $this->getAttributes(array(
							'alias',
							'name',
							'flypage',
							'category_parent_id',
						)) + array('level' => 1, 'active' => 1));
                return Yii::app()->db->lastInsertID;
	}

}

