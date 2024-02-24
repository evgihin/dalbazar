<?php

/**
 * Класс подсчитывает и хранит статистику перехода по категориям в механизме сессий
 */
class Category extends CFormModel {

	public $alias;
	public $name;
	public $flypage;
	public $template;

	public function attributeLabels() {
		return array(
			'alias' => 'Алиас',
			'name' => 'Имя категории',
			'flypage' => 'Шаблон категории',
			'template' => "Шаблон объявления",
		);
	}

	public function rules() {
		$regex = "/^[a-zA-Z0-9\-\_\.]+$/";
		$regex2 = "/^[a-zA-Z0-9а-яА-Я\s\-\_\.\/\\\(\)]+$/ui";
		return array(
			array('alias, flypage, name', "required", 'on' => 'edit, add'),
			array('alias, flypage', "match", 'pattern' => $regex, 'on' => 'edit, add'),
			array('name', "match", 'pattern' => $regex2, 'on' => 'edit, add'),
			array('flypage', "_inArray", 'array' => array_keys(Flypage::getList()), 'on' => 'edit, add'),
		);
	}

	public function _inArray($attr, $params) {
		if (!in_array($this->$attr, $params['array']))
			$this->addError($attr, 'Параметр должен быть одним из перечисленных');
	}

	public function update($categoryId) {
		Yii::app()->db->createCommand()
				->update('category', $this->getAttributes(array(
							'alias',
							'name',
							'flypage',
						)), 'category_id=:cid', array(':cid' => $categoryId));
	}

	public function insert() {
		Yii::app()->db->createCommand()
				->insert('category', $this->getAttributes(array(
							'alias',
							'name',
							'flypage',
						)) + array('level' => 0, 'active' => 1));
                return Yii::app()->db->lastInsertID;
	}

	/**
	 * зафиксировать факт перехода в категорию
	 * @param type $categoryId Ид категории, по которой зафиксировать переход
	 */
	public function detectUse($categoryId) {
		if (Yii::app()->user->hasState('mostUsedCategories')) {
			$used = Yii::app()->user->getState('mostUsedCategories');
		} else
			$used = array();
		if (isset($used[$categoryId]))
			$used[$categoryId]++; else
			$used[$categoryId] = 1;
		Yii::app()->user->setState('mostUsedCategories', $used);
	}

	/**
	 * Возвращает массив наиболее используемых категорий. Если не найдено столько используемых категорий - возвращает случайные из базы данных
	 * @param type $count
	 */
	public function getTopUsed($count) {
		$ids = array();
		if (Yii::app()->user->hasState('mostUsedCategories')) {
			$used = Yii::app()->user->getState('mostUsedCategories');
			arsort($used);
			foreach ($used as $id => $u) {
				if ($u > 0)
					$ids[] = $id;
			}
			array_splice($ids, $count);
		}
		if (count($ids) < $count) {
			$comm = Yii::app()->db->createCommand()->select('*')->from('category')->order('rand()');
			if ($ids)
				$comm->where(array('not in', 'category_id', $ids));
			$comm->limit(Yii::app()->params['countMenuItems'] - count($ids));
			$data = $comm->query();
			foreach ($data as $val) {
				$ids[] = (int) $val['category_id'];
			}
		}
		return $ids;
	}

	/**
	 * Удаляет статистику посещений пользователя для указанной категории
	 * @param int $categoryId Ид категории, статистику для которой обнулить (отчистить). Если $categoryId=-1 то будет удалена вся статистика посещений
	 */
	public function clear($categoryId = -1) {
		if ($categoryId == -1)
			Yii::app()->user->setState('mostUsedCategories', NULL);
		else {
			$used = Yii::app()->user->getState('mostUsedCategories');
			if (isset($used[$categoryId]))
				$used[$categoryId] = NULL;
			Yii::app()->user->setState('mostUsedCategories', $used);
		}
	}

	/**
	 * Получает список категорий из базы данных, применяя удобные фильтры
	 * @param mixed $parentId Ид родителя либо массив ИД-ов родителей. Если NULL то неважно какой родитель. По умолчанию NULL.
	 * @param int $level Уровень вложенности категорий. Если NULL неважно какой уровень. По умолчанию NULL.
	 * @param int $associative Вернуть в виде простого массива или ассоциативного(ключ - ID родителя). Если да, то функция вернет следующую структуру:
	 * (15=>((parent_id=>15,category_id=>12,name=>aaa,...),(parent_id=>15,category_id=>17,name=>bbb,...),...),
	 *  16=>((parent_id=>16,category_id=>10,name=>ccc,...),(parent_id=>16,category_id=>11,name=>ddd,...),...),
	 *  17=>((parent_id=>17,category_id=>54,name=>eee,...),(parent_id=>17,category_id=>62,name=>fff,...),...),(...) )
	 */
	public function get($parentId = NULL, $level = NULL, $associative = false) {
		$command = Yii::app()->db->createCommand()
				->select('*')
				->from('category')
				->order('pos ASC');

		$params = array();
		$val = array('AND', 'active=1');
		if (is_array($parentId))
			$val[] = array('in', 'category_parent_id', $parentId);
		if (is_int($parentId)) {
			$val[] = 'category_parent_id=:parentId';
			$params[':parentId'] = $parentId;
		}
		if ($level !== NULL)
			$val[] = 'level=:lev'; $params[':lev'] = $level;

		$command->where($val, $params);

		if ($associative) {
			$result = $command->query();
			$otv = array();
			foreach ($result as $r) {
				$otv[(int) $r['category_parent_id']][] = $r;
			}
			return $otv;
		} else
			return $command->queryAll();
	}

	/**
	 * Получить все родительские категории
	 * @return array массив катгорий
	 */
	public function getAllLevel1() {
		return Yii::app()->db->createCommand()
						->select('*')
						->from('category')
						->where('category_parent_id=0')
						->order('pos ASC')
						->where_and('active=1')
						->queryAll();
	}

	/**
	 * Получить все подкатегории
	 * @return array массив подкатегорий. Никак не упорядочен.
	 */
	public function getAllLevel2($parentId=NULL) {
		$comm = Yii::app()->db->createCommand()
				->select('*')
				->from('category')
				->where(array('AND','category_parent_id <> 0','active = 1'))
				->order('pos ASC');
		if ($parentId!=NULL)
			$comm->where_and ('category_parent_id=:cpi',array(':cpi'=>$parentId));
		return $comm->queryAll();
	}

	/**
	 * Получить информацию о категории по её ИД-у
	 * @param mixed $categoryId ид категории, либо массив ИД-ов для которых получить информацию
	 * @return array ассоциативный массив с параметрами категории если был задан один ИД, и массив параметров если было задано несколько значений
	 */
	public function getByCategory($categoryId) {
		if (!is_array($categoryId)) {
			$categories = array($categoryId);
		} else
			$categories = $categoryId;
		$command = Yii::app()->db->createCommand()
				->select('*')
				->from('category')
				->where(array('IN', 'category_id', $categories));
		if (!is_array($categoryId))
			return $command->queryRow();
		else
			return $command->queryAll();
	}
        
        public function getByAdvert($advertId){
            $command = Yii::app()->db->createCommand()
				->select('category.*')
				->from('category')
                                ->join("advert", "advert.category_id=category.category_id")
				->where('advert_id=:aid', array(":aid"=>$advertId));
            return $command->queryRow();
        }

	/**
	 * Получает массив ИД-ов категорий
	 * @param type $categoryList Массив категорий, полученный через Category::get();
	 * @return array Массив ID-ов катгорий
	 */
	public function getIdArray($list) {
		$ids = array();
		foreach ($list as $c) {
			if (isset($c['category_id']))
				$ids[] = $c['category_id'];
		}
		return $ids;
	}

	/**
	 * Получить ТОП-список категорий из БД
	 * @param mixed $parentId ИД родительской категории, в которой искать, либо массив категорий. Если NULL - то получить все топовые категории
	 * @return array Массив топовых категорий
	 */
	public function getTopProducts($parentId = NULL) {
		$command = Yii::app()->db->createCommand()
				->select('*')
				->from('category')
				->order('top ASC');
		$val = array('AND', 'NOT ISNULL (top)');
		if (is_array($parentId))
			$val[] = array('IN', 'category_parent_id', $parentId);
		if (is_int($parentId))
			$val[] = 'category_parent_id=:parentId';

		$command->where($val, array(':parentId' => (int) $parentId));

		$res = $command->query();
		$otv = array();
		foreach ($res as $r) {
			if (is_int($parentId))
				$otv = $r; else
				$otv[(int) $r['category_parent_id']][] = $r;
		}
		return $otv;
	}

	/**
	 * Парсит ИД категории из $_GET массива. В $_GET массиве может
	 * @return mixed <b>false</b> если запрошенной категории не найдено в БД,<br>
	 * <b>-1</b> если признаки категорий не найдены в строке запроса,<br>
	 * <b>ИД категории</b> если категория найдена.
	 */
	public function idFromRequest() {
		if (isset($_GET['alias3'])) {
			$idStr = $_GET['alias3'];
			$level = 2;
			$pId = $_GET['alias2'];
		} elseif (isset($_GET['alias2'])) {
			$idStr = $_GET['alias2'];
			$level = 1;
			$pId = $_GET['alias1'];
		} elseif (isset($_GET['alias1'])) {
			$idStr = $_GET['alias1'];
			$level = 0;
		} else
			return -1;
		$where = array("AND", "level=:level");
		$params = array(':level' => $level);
		//если в параметре передано число
		if (preg_match('/^\d+$/', $idStr))
			return (int) $idStr;
		else {
			$where[] = 'alias=:alias';
			$params[':alias'] = $idStr;
		}

		//для всех элементов кроме корневого необходимо проверить алиас родителя, либо его ИД
		if ($level !== 0) {
			//если в родителе передано число
			if (preg_match('/^\d+$/', $pId)) {
				$where[] = "category_parent_id=:pAlias";
				$params[':pAlias'] = (int) $pId;
			} else {
				$where[] = "category_parent_id=(SELECT category_id FROM category WHERE alias=:pAlias AND level=:pLevel)";
				$params[':pAlias'] = $pId;
				$params[':pLevel'] = $level - 1;
			}
		}
		$res = Yii::app()->db->createCommand()
				->select("category_id")
				->from('category')
				->where($where, $params)
				->limit(1)
				->queryScalar();
		return (is_bool($res)) ? $res : (int) $res;
	}

	public function checkAvailable($categoryId) {
		return Yii::app()->db->createCommand()
						->select()
						->from('category')
						->where('category_id=:cid', array(":cid" => $categoryId))
						->queryScalar();
	}

	public function setPos($categoryId, $pos) {
		Yii::app()->db->createCommand()
				->update('category', array('pos' => $pos), 'category_id=:cid', array(':cid' => $categoryId));
	}

	public function remove($categoryId) {
		$arr = $categoryId;
		if (!is_array($categoryId))
			$arr = array($categoryId);
		Yii::app()->db->createCommand()
				->update('category', array('active' => 0), array('IN', 'category_id', $arr));
	}

}

;
