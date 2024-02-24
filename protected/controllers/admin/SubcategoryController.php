<?php

class SubcategoryController extends CAdminController {

	private $_subcategory = false;

	function actionList($parent_id = NULL) {
		if ($parent_id !== NULL && !Helpers::checkId($parent_id))
			throw new CHttpException(400, 'Неверно указан родительский ИД');
		$category = new Category();
		$categories = $category->getAllLevel2($parent_id);
		$parents = $category->getAllLevel1();
		$this->render('list', array(
			'categories' => $categories,
			'parents' => Helpers::toAssoc($parents, 'category_id'),
			'parent_id' => $parent_id,
		));
	}

	function actionAdd() {
		if (!$this->_subcategory)
			$this->_subcategory = new SubCategory("add");
		$category = new Category();
		$cats = $category->getAllLevel1();
		$catL1 = array();
		foreach ($cats as $val) {
			$catL1[$val['category_id']] = $val['name'];
		}
		$this->render('add', array(
			'category' => $this->_subcategory,
			'parents' => $catL1,
		));
	}

	function actionInsert() {
		if (!Helpers::required($_POST, array('SubCategory'), false))
			throw new CHttpException(400, 'Не заданы параметры формы');
		$this->_subcategory = new SubCategory("add");
		$this->_subcategory->attributes = $_POST['SubCategory'];

		if ($this->_subcategory->validate()) {
			$subcategoryId = $this->_subcategory->insert();
                        Log::admin("subCategory/insert",array("subcategory_id"=>$subcategoryId), "Подкатегория создана");
			if (isset($_POST['action']) && $_POST['action'] == "saveAdd")
				$this->redirect(array('admin/subcategory/add'));
			else
				$this->redirect(array('admin/subcategory/list'));
		} else
			$this->actionAdd();
	}

	function actionEdit($category_id) {
		Helpers::requiredId($category_id);
		$category = new Category();

		if (!$this->_subcategory) {
			$this->_subcategory = new SubCategory('edit');
			$this->_subcategory->attributes = $category->getByCategory($category_id);
		}

		$cats = $category->getAllLevel1();
		$catL1 = array();
		foreach ($cats as $val) {
			$catL1[$val['category_id']] = $val['name'];
		}
		$this->render('edit', array(
			'category' => $this->_subcategory,
			'parents' => $catL1,
			'id' => $category_id
		));
	}

	function actionUpdate($category_id) {
		Helpers::requiredId($category_id);
		$this->_subcategory = new SubCategory('edit');
		if (!isset($_POST['SubCategory']))
			throw new CHttpException(400, 'Не заданы параметры формы');
		$this->_subcategory->attributes = $_POST['SubCategory'];

		if ($this->_subcategory->validate()) {
			$this->_subcategory->update($category_id);
                        Log::admin("subCategory/update",array("subcategory_id"=>$category_id), "Подкатегория обновлена");
			if (isset($_POST['action'])) {
				if ($_POST['action'] == "saveAdd")
					$this->redirect(array('admin/subcategory/add')); else
				if ($_POST['action'] == "apply")
					$this->redirect(array('admin/subcategory/edit', 'category_id' => $category_id));
				else
					$this->redirect(array('admin/subcategory/list'));
			} else
				$this->redirect(array('admin/subcategory/list'));
		} else
			$this->actionEdit($category_id);
	}

	function actionDo() {
		if (!Helpers::required($_POST, 'action', false))
			throw new CHttpException(400, 'Не указано действие');
		$category = new Category();

		switch ($_POST['action']) {
			case 'save': //обновляем позиции
				$pos = (!empty($_POST['pos'])) ? $_POST['pos'] : array();
				foreach ($pos as $id => $val) {
					if (Helpers::checkId($id) && Helpers::checkId($val)) {
						$category->setPos($id, $val);
					}
				}
                                Log::admin("subCategory/doSave",array("count"=>  count($pos)), "Изменен порядок подкатегорий");
				break;
			case 'delete': //удаляем категорию
				$rem = (!empty($_POST['select'])) ? $_POST['select'] : array();
				$category->remove(array_keys($rem, 'on'));
                                Log::admin("subCategory/doRemove",array("count"=>  count($rem)), "Удалены подкатегории");
				break;
		}
		$this->redirect(array('admin/subcategory/list'));
	}

}
