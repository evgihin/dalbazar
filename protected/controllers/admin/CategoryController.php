<?php

class CategoryController extends CAdminController {

	function actionList() {
		$category = new Category();
		$this->render('list', array(
			'categories' => $category->getAllLevel1(),
		));
	}

	function actionEdit($category_id) {
		if (!Helpers::checkId($category_id))
			throw new CHttpException(400, 'Неверно задан ИД категории');
		$categoryId = $category_id;
		unset($category_id); //для чистки кармы
		$category = new Category("edit");
		$this->render('edit', array(
			'category' => $category->getByCategory($categoryId)
		));
	}

	function actionUpdate($category_id) {
		if (!Helpers::checkId($category_id) || !Helpers::required($_POST, array('cat'),false))
			throw new CHttpException(400, 'Неверно задан ИД категории или параметры формы');
		$categoryId = $category_id;

		$category = new Category('edit');
		$category->attributes = $_POST['cat'];
		if ($category->validate()) {
			$category->update($categoryId);
                        Log::add("admin/category/update",array("category_id"=>$categoryId), "обновлена категория");
			$this->redirect(array('admin/category/list'));
		} else
			$this->redirect(array('admin/category/edit', array('category_id' => $categoryId)));
	}

	function actionAdd() {
		$category = new Category("add");
		$this->render('add', array(
			'category' => $category,
		));
	}

	function actionInsert() {
		if (!Helpers::required($_POST, array('Category'), false))
			throw new CHttpException(400, 'Не заданы параметры формы');
		$category = new Category("add");
		$category->attributes = $_POST['Category'];

		if ($category->validate()) {
			$id = $category->insert();
                        Log::add("admin/category/insert",array("category_id"=>$id), "обновлена категория");
			$this->redirect(array('admin/category/list'));
		} else
			$this->render('add', array(
				'category' => $category,
			));
	}

	function actionDo() {
		if (!Helpers::required($_POST, 'action', false))
			throw new CHttpException(400, 'Не указано действие');
		$category = new Category();

		switch ($_POST['action']) {
			case 'save': //обновляем позиции
				$pos = (!empty($_POST['pos'])) ? $_POST['pos'] : array();
                                Log::add("admin/category/setpos",array("category_id_array"=>$pos), "изменен порядок категорий");
				foreach ($pos as $id => $val) {
					if (Helpers::checkId($id) && Helpers::checkId($val)) {
						$category->setPos($id, $val);
					}
				}
				break;
			case 'delete': //удаляем категорию
				$rem = (!empty($_POST['select'])) ? $_POST['select'] : array();
				$category->remove(array_keys($rem, 'on'));
                                Log::add("admin/category/delete_arr",array("category_id_array"=>array_keys($rem, 'on')), "удалено несколько категорий");
				break;
		}
		$this->redirect(array('admin/category/list'));
	}

}
