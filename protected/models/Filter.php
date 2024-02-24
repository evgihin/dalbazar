<?php

/**
 * Класс управления фильтрами
 */
class Filter extends CExtFormModel {

    var $order = "pos ASC";

    public function rules() {
        return array(
        );
    }

    /**
     * Получает список фильтров, принадлежащих категории $categoryId
     * @param mixed $categoryId ИД категории, для которой получить фильтры. Работают категории любых уровней
     * @return array Массив фильтров
     */
    public function getByCategory($categoryId) {
        $arr = $categoryId;
        if (!is_array($categoryId))
            $arr = array($categoryId);
        $res = Yii::app()->db->createCommand()
                ->select('filter.*')
                ->from('filter')
                ->join('filter_category_xref', 'filter.filter_id=filter_category_xref.filter_id')
                ->where(array('AND', 'filter.removed=0', array('IN', 'category_id', $arr)))
                ->order(array('pos ASC', 'name ASC'));
        return $res->queryAll();
    }

    /**
     * Получает значения всех фильтров в массиве "ключ-значение" для объявления независимо от типа фильтра. Value уже содержит и значение и единицу измерения
     * @param int $advertId ИД объявления, для которого получить массив фильтров
     * @return array Массив фильтров
     */
    public function getValuesByAdvert($advertId) {
        return Yii::app()->db->createCommand()
                        ->select()
                        ->from('filter_value')
                        ->where('advert_id=:aid', array(':aid' => $advertId))
                        ->queryAll();
    }
    
    /**
     * получает значени параметров объявления в формате array(filter_id=>param_id, filter_id=>value). Никакой полной информации в отличие от getValuesByAdvert
     * @param int $advertId
     */
    public function getAdvertValues($advertId){
        $vals = Yii::app()->db->createCommand()
                ->select()
                ->from('advert_filter')
                ->where('advert_id=:aid',array(":aid"=>$advertId))
                ->query();
        $ret = array();
        foreach ($vals as $value) {
            $ret[$value["filter_id"]] = ($value['filter_param_id'])?$value['filter_param_id']:$value['value'];
        }
        return;
    }

    /**
     * То же самое что и getByCategory, только получает ТОЛЬКО зависимые фильтры, принадлежащие категории $categoryId
     * @param mixed $categoryId ИД категории, для которой получить фильтры. Работают категории любых уровней
     * @return array Массив фильтров
     */
    public function getByCategoryOnlyDepend($categoryId) {
        $arr = $categoryId;
        if (!is_array($categoryId))
            $arr = array($categoryId);
        $res = Yii::app()->db->createCommand()
                ->select('filter.*')
                ->from('filter')
                ->join('filter_category_xref', 'filter.filter_id=filter_category_xref.filter_id')
                ->where(array('AND', 'filter.removed=0', 'filter.depend!=0', array('IN', 'category_id', $arr)))
                ->order(array('pos ASC', 'name ASC'));
        return $res->queryAll();
    }

    public function getByFilter($filterId) {
        $arr = $filterId;
        if (!is_array($filterId))
            $arr = array($filterId);
        $res = Yii::app()->db->createCommand()
                ->select('*')
                ->from('filter')
                ->where(array('IN', 'filter_id', $arr));
        if (!is_array($filterId))
            return $res->queryRow();
        else
            return $res->queryAll();
    }

    public function getByAdvert($advertId) {
        $comm = Yii::app()->db->createCommand()
                ->select("filter.*")
                ->from('filter')
                ->join('filter_category_xref', 'filter.filter_id=filter_category_xref.filter_id')
                ->join("advert", "advert.category_id=filter_category_xref.category_id")
                ->where(array('AND', 'filter.removed=0', 'advert_id=:aid'), array(":aid" => $advertId))
                ->order(array('pos ASC', 'name ASC'));
        return $comm->queryAll();
    }

    public function getDepends(array $filterIdList) {
        return Yii::app()->db->createCommand()
                        ->select('*')
                        ->from('filter_depending')
                        ->where(array('IN', 'filter_param_id', $filterIdList))
                        ->queryAll();
    }

    public function getRelations($filterIdList = NULL) {
        $comm = $this->createCommand()
                ->select('filter_category_xref.*')
                ->from('filter_category_xref');
        if ($filterIdList) {
            if (!is_array($filterIdList))
                $filterIdList = array($filterIdList);
            $filterIdList->where(array('IN', 'filter_category_xref.filter_id', $filterIdList));
        }
        $comm->join("filter", "filter.filter_id = filter_category_xref.filter_id")
                ->order("filter.pos ASC");

        return $comm->queryAll();
    }

    public function saveDepends(array $depends) {
        $command = Yii::app()->db->createCommand();

        foreach ($depends as $id => $val) {
            $command->setText('INSERT INTO filter_depending SET filter_param_id = :fpi, filter_depending_param_id=:fdpi ON DUPLICATE KEY UPDATE filter_depending_param_id=:fdpi');
            $command->execute(array(':fpi' => $id, ':fdpi' => $val));
        }
    }

    public function getOnlyType($type) {
        return Yii::app()->db->createCommand()
                        ->select('filter.*')
                        ->from('filter')
                        ->where('type=:t', array(':t' => $type))
                        ->queryAll();
    }

    /**
     * Получить полную информацию о фильтрах, включая имя зависимого фильтра, номер категории и количество параметров
     * @param int $categoryId ИД категории, для которой получить фильтры
     * @return array массив фильтров. Не ассоциативный!
     */
    public function getFullByCategory($categoryId) {
        return Yii::app()->db->createCommand()
                        ->select(array(
                            "a.*",
                            "b.category_id",
                            new CDbExpression("(SELECT COUNT(d.filter_param_id)
                    FROM filter_param AS d WHERE d.filter_id = a.filter_id AND d.removed = 0) as params"),
                            new CDbExpression("(SELECT c.name FROM filter AS c WHERE c.filter_id = a.depend) AS depend_name")
                        ))
                        ->from('filter AS a')
                        ->join("filter_category_xref AS b", "b.filter_id=a.filter_id")
                        ->where(array('AND', 'category_id=:categoryId', 'a.removed=0'), array(':categoryId' => $categoryId))
                        ->queryAll();
    }

    /**
     * Получить полную информацию о ВСЕХ фильтрах, включая имя зависимого фильтра и количество параметров. ИДы категорий не получает!
     * @return array массив фильтров. Не ассоциативный!
     */
    public function getFullAll() {
        return Yii::app()->db->createCommand()
                        ->select(array(
                            "a.*",
                            new CDbExpression("(SELECT COUNT(d.filter_param_id)
                    FROM filter_param AS d WHERE d.filter_id = a.filter_id AND d.removed = 0) as params"),
                            new CDbExpression("(SELECT c.name FROM filter AS c WHERE c.filter_id = a.depend) AS depend_name")
                        ))
                        ->from('filter AS a')
                        ->where("a.removed=0")
                        ->queryAll();
    }

    /**
     * Получить полную информацию об удаленных фильтрах, включая имя зависимого фильтра и количество параметров. ИДы категорий не получает!
     * @return array массив фильтров. Не ассоциативный!
     */
    public function getFullRemoved() {
        return Yii::app()->db->createCommand()
                        ->select(array(
                            "a.*",
                            new CDbExpression("(SELECT COUNT(d.filter_param_id)
                    FROM filter_param AS d WHERE d.filter_id = a.filter_id AND d.removed = 0) as params"),
                            new CDbExpression("(SELECT c.name FROM filter AS c WHERE c.filter_id = a.depend) AS depend_name")
                        ))
                        ->from('filter AS a')
                        ->where("a.removed=1")
                        ->queryAll();
    }

    /**
     * Устанавливает значения фильтров для объявления
     * @param int $advertId ИД объявления
     * @param array $values Параметры в формате array(filter_id=>param_id, filter_id=>value)
     */
    public function setValues($advertId, $values) {
        $cCategory = new Category();
        $categoryId = $cCategory->getByAdvert($advertId)['category_id'];
        $filters = $this->getByCategory($categoryId);
        $advertFilters = $this->getAdvertValues($advertId);

        foreach ($filters as $filter) {
            if (isset($values[$filter['filter_id']])) {
                if (isset($advertFilters[$filter['filter_id']])) {
                    if ($filter['type'] == 's')
                        Yii::app()->db->createCommand()
                                ->update("advert_filter", array("filter_param_id" => $values[$filter['filter_id']]), "advert_id=:aid AND filter_id=:fid", array(":aid" => $advertId, ":fid" => $filter['filter_id']));
                    elseif ($filter['type'] == 'i')
                        Yii::app()->db->createCommand()
                                ->update("advert_filter", array("value" => $values[$filter['filter_id']]), "advert_id=:aid AND filter_id=:fid", array(":aid" => $advertId, ":fid" => $filter['filter_id']));
                } else {
                    if ($filter['type'] == 's')
                        Yii::app()->db->createCommand()
                                ->insert("advert_filter", array(
                                    "filter_param_id" => $values[$filter['filter_id']], 
                                    "advert_id" => $advertId,
                                    "filter_id" => $filter['filter_id'],
                            ));
                    elseif ($filter['type'] == 'i')
                        Yii::app()->db->createCommand()
                                ->insert("advert_filter", array(
                                    "value" => $values[$filter['filter_id']], 
                                    "advert_id" => $advertId,
                                    "filter_id" => $filter['filter_id'],
                            ));
                }
            }
        }
    }

    /**
     * Получить список параметров
     * @param mixed $ids ИД либо массив ИД-ов фильтров, для которых выдать параметры
     * @param int $depending ИД параметра фильтра, от которого зависят возвращаемые параметры. Если 0 то выдадутся только независимые фильтры
     * @return array Массив ID-ов фильтров
     */
    public function getParam($ids, $depending = 0) {
        if (is_int($ids)) {
            $ids = array($ids);
        }
        $command = Yii::app()->db->createCommand()
                ->select('filter_param.*')
                ->from('filter_param')
                ->join('filter', 'filter.filter_id=filter_param.filter_id')
                ->order('pos ASC', 'name ASC');

        $where = array('AND', array('IN', 'filter_param.filter_id', $ids), 'filter_param.removed=0');
        $params = array();

        $where[] = 'filter.depend=:depending';
        $params[':depending'] = (int) $depending;

        $command->where($where, $params);
        return $command->queryAll();
    }

    /**
     * Получить список параметров, по их ид-ам
     * @param mixed $param_id ид либо массив ид-ов параметров, анные которых надо получить
     * @return array данные о параметре, либо о наборе параметров
     */
    public function getParamByParam($param_id) {
        $arr = $param_id;
        if (!is_array($param_id))
            $arr = array($param_id);
        $res = Yii::app()->db->createCommand()
                ->select('filter_param.*')
                ->from('filter_param')
                ->where(array('AND', array('IN', 'filter_param.filter_param_id', $arr), 'filter_param.removed=0'))
                ->order('pos ASC', 'name ASC');
        if (!is_array($param_id))
            return $res->queryRow();
        else
            return $res->queryAll();
    }

    /**
     * Получить список параметров для фильтра
     * @param int $filter_id ИД фильтра, для которого получать параметры
     * @return array Массив параметров
     */
    public function getAllParam($filter_id) {
        $filter_id = (int) $filter_id;
        return Yii::app()->db->createCommand()
                        ->select('filter_param.*')
                        ->from('filter_param')
                        ->where(array('AND', 'removed=0', 'filter_id=:fid'), array(':fid' => $filter_id))
                        ->order('pos ASC')
                        ->queryAll();
    }

    /**
     * Получает список зависимых параметров от значения.
     * @param type $param_id ИД параметра, от которого зависит значение фильтра
     * @param type $filter_id ИД фильтра, который зависит от выбранного параметра
     * @return array список зависимых параметров фильтра filter_id
     */
    public function getDependingFromVal($param_id, $filter_id) {
        return Yii::app()->db->createCommand()
                        ->select('filter_param.*')
                        ->join('filter_depending', 'filter_depending.filter_param_id=filter_param.filter_param_id')
                        ->where(array('AND', 'filter_depending.filter_depending_param_id=:fdpi', 'filter_param.filter_id=:fid', 'filter_param.removed=0'), array(
                            ':fdpi' => $param_id,
                            ':fid' => $filter_id,
                        ))
                        ->from('filter_param')
                        ->order('filter_param.filter_id ASC')
                        ->queryAll();
    }

    /**
     * Возвращает список категорий, от которых зависит фильтр
     * @param int $filterId ИД зависимого фильтра
     * @return array массив зависимостей
     */
    public function getDependCategories($filterId) {
        return Yii::app()->db->createCommand()
                        ->select('*')
                        ->from('filter_category_xref')
                        ->where('filter_id=:fid', array(':fid' => $filterId))
                        ->queryAll();
    }

    public function setDependCategories($filterId, $catList) {
        if (!$catList)
            throw new CHttpException(400, 'В списке категорий должна быть как синимум одна категория');
        $command = Yii::app()->db->createCommand();
        //проверяем на существование все категории и получаем только существующие
        $catList = $command->select('category_id')->from('category')->where(array('IN', 'category_id', $catList))->queryColumn();
        $command->reset();
        if (!$catList)
            throw new CHttpException(400, 'Указаны ИД-ы несуществующих категорий');

        //затем вычисляем их родителей
        $parentList = $command
                ->select('category.category_parent_id')
                ->from('category')
                ->where(array('AND', array('IN', 'category_id', $catList), 'category_parent_id <>0'))
                ->group('category_parent_id')
                ->queryColumn();

        //сливаем всё в один список
        $catList = array_merge($catList, $parentList);

        //сначала чистим все зависимости для этого фильтра
        $command->delete('filter_category_xref', 'filter_id=:fid', array(':fid' => $filterId));

        //затем добавляем список категорий в таблицу зависимостей
        foreach ($catList as $catId) {
            $command->insert('filter_category_xref', array('filter_id' => $filterId, 'category_id' => $catId));
        }
    }

    /**
     * Обновить порядок следования фильтров
     * @param array $posList массив позиций в формате id=>pos
     */
    public function updatePos($posList) {
        $command = Yii::app()->db->createCommand();
        foreach ($posList as $id => $val) {
            $id = (int) $id;
            $val = (int) $val;
            if (!$id || !$val)
                continue;
            $command->update('filter', array('pos' => $val), 'filter_id=:fid', array(':fid' => $id));
        }
    }

    /**
     * Обновить порядок следования параметров для фильтров
     * @param array $posList массив позиций в формате id=>pos
     */
    public function updateParamPos($posList) {
        $command = Yii::app()->db->createCommand();
        foreach ($posList as $id => $val) {
            $id = (int) $id;
            $val = (int) $val;
            if (!$id || !$val)
                continue;
            $command->update('filter_param', array('pos' => $val), 'filter_param_id=:fpid', array(':fpid' => $id));
        }
    }

    /**
     * Обновляет параметры фильтра.
     * @param type $params  Список задан в виде ассоциативного массива,
     * где ИД-ом является ИД параметра
     */
    public function updateParam($params) {
        $command = Yii::app()->db->createCommand();
        foreach ($params as $id => $name) {
            $id = (int) $id;
            if (!$id)
                continue;
            $command->update(
                    'filter_param', array('name' => $name), 'filter_param_id=:fpi', array(':fpi' => $id)
            );
        }
    }

    /**
     * Добавить параметры к фильтру
     * @param type $params список параметров в виде обычного массива
     * @param type $filterId ИД фильтра, к которому добавлять параметры
     */
    public function addParam($params, $filterId) {
        $filterId = (int) $filterId;
        if (!$filterId)
            return;
        $command = Yii::app()->db->createCommand();
        foreach ($params as $name) {
            if (!$name)
                continue;
            $command->insert('filter_param', array(
                'filter_id' => $filterId,
                'name' => $name,
            ));
        }
    }

    public function removeParam($params) {
        $command = Yii::app()->db->createCommand();
        foreach ($params as $id => $name) {
            $id = (int) $id;
            if (!$id)
                continue;
            $command->update('filter_param', array('removed' => 1), 'filter_param_id=:fpi', array(':fpi' => $id));
        }
    }

    /**
     * удалить из базы данных фильтры с указанными Ид-ами
     * @param array $ids массив ИД-ов, указывается в формате $id=>'on'.
     */
    public function delete($ids) {
        $command = Yii::app()->db->createCommand();
        foreach ($ids as $id => $val) {
            $id = (int) $id;
            if (!$id || $val !== 'on')
                continue;
            $command->update('filter', array('removed' => true), 'filter_id=:fid', array(':fid' => $id));
        }
    }

    /**
     * восстановить в базе данных фильтры с указанными Ид-ами
     * @param array $ids массив ИД-ов, указывается в формате $id=>'on'.
     */
    public function undelete($ids) {
        $command = Yii::app()->db->createCommand();
        foreach ($ids as $id => $val) {
            $id = (int) $id;
            if (!$id || $val !== 'on') {
                continue;
            }
            $command->update('filter', array('removed' => FALSE), 'filter_id=:fid', array(':fid' => $id));
        }
    }

    /**
     * Обновить общие данные фильтра
     * @param type $filterId ИД обновляемого фильтра
     * @param type $name Имя фильтра
     * @param type $type Тип фильтра (I или S)
     */
    public function updateFilter($filterId, $name, $type) {
        $filterId = (int) $filterId;
        if (!$filterId || ($type != 's' && $type != 'i'))
            return false;
        $command = Yii::app()->db->createCommand();
        $command->update('filter', array('name' => $name, 'type' => $type), 'filter_id=:fid', array(':fid' => $filterId));
        return true;
    }

    /**
     * Обновить фильтр выбора
     * @param type $filterId ИД фильтра
     * @param type $depend ИД фильтра, от которого он зависит
     */
    public function updateFilterS($filterId, $depend, $topCount) {
        $filterId = (int) $filterId;
        $depend = (int) $depend;
        if (!$filterId)
            return false;
        $command = Yii::app()->db->createCommand();
        $command->update('filter', array('depend' => $depend, 'top_count' => $topCount), 'filter_id=:fid', array(':fid' => $filterId));
        return true;
    }

    /**
     * Обновить численный фильтр
     * @param type $filterId ИД обновляемого фильтра
     * @param type $from
     * @param type $to
     * @param type $step
     * @param type $piece
     */
    public function updateFilterI($filterId, $from, $to, $step, $piece) {
        $filterId = (int) $filterId;
        $from = (int) $from;
        $to = (int) $to;
        $step = (int) $step;
        if (!$filterId)
            return false;
        $command = Yii::app()->db->createCommand();
        $command->update('filter', array('from' => $from, 'to' => $to, 'step' => $step, 'piece' => $piece), 'filter_id=:fid', array(':fid' => $filterId));
        return true;
    }

    /**
     * Обновить общие данные фильтра
     * @param type $filterId ИД обновляемого фильтра
     * @param type $name Имя фильтра
     * @param type $type Тип фильтра (I или S)
     */
    public function addFilter($name, $type) {
        if ($type != 's' && $type != 'i')
            return false;
        $command = Yii::app()->db->createCommand();
        $res = $command->insert('filter', array('name' => $name, 'type' => $type));
        return Yii::app()->db->lastInsertID;
    }

    /**
     * Проверяет существуют ли параметры фильтров в базе данных и совпадают ли они фильтрам.
     * @param type $filterS
     * @return boolean true если все параметры совпали и false если хотя бы один из них не совпал
     */
    public function checkValuesS($filterS) {
        $command = Yii::app()->db->createCommand()
                ->select("count(*)")
                ->from('filter_param');
        $i = 0;
        foreach ($filterS as $id => $val) {
            $command->where_or(array("AND", "filter_id=:fid" . $i, "filter_param_id=:fpid" . $i), array(':fid' . $i => $id, ':fpid' . $i => $val));
            $i++;
        }
        if ($command->queryScalar() == count($filterS))
            return true;
        else
            return false;
    }

    /**
     * проверить подходят ли значения фильтров под указанные рамки
     * @param type $filterI
     * @return boolean true если все параметры совпали и false если хотя бы один из них не совпал
     */
    public function checkValuesI($filterI) {
        $command = Yii::app()->db->createCommand()
                ->select("count(*)")
                ->from('filter');
        $i = 0;
        foreach ($filterI as $id => $val) {
            $command->where_or(array('AND', "filter_id = :fid" . $i, "filter.from <= :val" . $i, "filter.to >= :val" . $i), array(
                ':fid' . $i => $id,
                ':val' . $i => $val,
            ));
            $i++;
        }
        $command->where_and("type='i'");
        if ($command->queryScalar() == count($filterI))
            return true;
        else
            return false;
    }

    /**
     * Чистит все фильтры для определенного объявления
     * @param int $advertId ИД объявления, для которого почистить фильтры
     */
    public function clearByAdvert($advertId) {
        Yii::app()->db->createCommand()
                ->delete('advert_filter', 'advert_id=:aid', array(':aid' => $advertId));
    }

    public function getDependentCategory($filterId) {
        if (!is_array($filterId))
            $filterId = array($filterId);

        return Yii::app()->db->createCommand()
                        ->select()
                        ->from('filter_category_xref')
                        ->where(array('IN', 'filter_id', $filterId))
                        ->queryAll();
    }

    /**
     * Преобразовывает и проверяет на верность значения фильтров из запроса (с checkbox)
     * в пригодную для использования форму
     */
    public static function validateSFromQuery($filters) {
        return Yii::app()->db->createCommand()
                        ->select('filter_param_id')
                        ->from('filter_param')
                        ->where(array('IN', 'filter_param_id', array_keys($filters)))
                        ->queryColumn();
    }

}
