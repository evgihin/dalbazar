<?php

class ParamController extends CAdminController {

	var $_model = Null;

	function actionAddDepend($filter_id) {
		Helpers::requiredId($filter_id);

		$param = new Param();
		$filter = new Filter();

		$dependFilterId = $filter->getByFilter($filter_id)['depend'];
		if (!$dependFilterId)
			throw new CHttpException(400, 'Этот фильтр не от чего не зависит');

		$temp = $param->getByFilter($dependFilterId);
		foreach ($temp as $val) {
			$requiredParams[$val['filter_param_id']] = $val['name'];
		}


		$this->render('addDepend', array(
			'requiredParams' => $requiredParams,
			'filterId' => $filter_id
		));
	}

	function actionInsertDepend($filter_id) {
		Helpers::requiredId($filter_id);

		if (empty($_POST['param']) || empty($_POST['depend']))
			throw new CHttpException(400, "не заданы параметры запроса");

		$dependId = $_POST['depend'];

		foreach ($_POST['param'] as $val) {
			$param = new Param('add');
			$param->attributes = $val;
			$param->filter_id = $filter_id;
			if ($param->validate()){
				$newId = $param->insert();

				//создаем зависимость
				$depend = new Depend('add');
				$depend->filter_param_id = $newId;
				$depend->filter_depending_param_id = $dependId;
				$depend->insert();
                                
                                Log::admin("param/InsertDepend",array("filter_id"=>$filterId, "filter_param_id"=>$newId), "Добавлен новый зависимый параметр фильтра");
			}
		}

		UCHtml::activeDropDownListSaveDefault('depend', $dependId);
		$this->redirect(array('admin/filter/depend', 'filterId' => $filter_id));
	}

}

