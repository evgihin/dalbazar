<?php

class FilterController extends CFrontEndController {

  public $left;

  /**
   * Возвращает фильтры для выбранной категории
   * @param int $advert_id ИД объявления. Если указан то подставляет в фильтры значения из объявления
   * @throws CHttpException
   */
  public function actionGetByCategory($advert_id = '') {
    if (!isset($_POST['categoryId']))
      throw new CHttpException(400, 'Необходимо указать ИД категориии');
    $categoryId = $_POST['categoryId'];
    $advertId = $advert_id; //по фэнь-шую
    if (!Helpers::checkId($categoryId))
      throw new CHttpException(400, 'ИД категории задан неправильно');
    if ($advert_id && !Helpers::checkId($advertId))
      throw new CHttpException(400, 'ИД объявления задан неправильно');
    if (isset($_POST['fieldName']) && preg_match("/^[a-z0-9\_.\-]+$/i", $_POST['fieldName']))
      $fieldName = $_POST['fieldName'];
    else
      $fieldName = 'filter';


    $filter = new Filter();
    $filters = $filter->getByCategory($categoryId);

    $param = new Param();
    $params = $param->getByFilter(Helpers::getIdArray($filters, 'filter_id'));
    $params = Helpers::toAssoc($params, 'filter_id');

    $values = false;
    if ($advertId) {
      $values = Helpers::toAssoc($param->getValues($advertId), 'filter_id');
    }

    $this->renderPartial('getByCategory', array(
        'filters' => $filters,
        'params' => $params,
        'values' => ($values) ? $values : array(),
        'fieldName' => $fieldName,
        'categoryId' => $categoryId,
        'advertId' => $advertId
    ));
  }

  /**
   * Будет удалено
   * @todo удалить реализацию и перевести добавление объявлений на новый функционал
   * @param type $advert_id
   * @throws CHttpException
   */
  public function actionGetDependByParam($advert_id = '') {
    if (!isset($_POST['id']))
      throw new CHttpException(400, 'Необходимо указать ИД фильтра');

    $filterId = (int) $_POST['id'];
    if (!Helpers::checkId($filterId))
      throw new CHttpException(400, 'ИД фильтра задан неверно');

    $advertId = $advert_id; //по фэнь-шую
    if ($advert_id && !Helpers::checkId($advertId))
      throw new CHttpException(400, 'ИД объявления задан неправильно');

    if (!isset($_POST['value']))
      throw new CHttpException(400, 'Необходимо указать значение зависимого параметра');

    $paramId = (int) $_POST['value'];
    $filter = new Filter();
    $params = $filter->getDependingFromVal($paramId, $filterId); //@todo перекинуть в другую модель params

    $param = new Param();
    $value = false;
    if ($advertId) {
      $values = Helpers::toAssoc($param->getValues($advertId), 'filter_id');
      if (isset($values[$filterId]['filter_param_id']))
        $value = $values[$filterId]['filter_param_id'];
    }

    $this->renderPartial('getDependByParam', array(
        'params' => $params,
        'value' => $value
    ));
  }
  
  /**
   * Выдает на экран список зависимых параметров, которые зависят от $value значения
   * @param int $filter
   * @param int $value
   * @throws CHttpException
   */
  public function actionGetDependentParams($filter, $value, $selected=0){
      
      $cFilter = new Filter();
      $filter = $cFilter->getByFilter($filter);
      
      $value = (int)$value;
      $selected = (int)$selected;
      if (!$value || !$filter)
          throw new CHttpException(400,"Параметр задан неверно");
      
      $cParam = new Param();
      $params = $cParam->getParamByDependParam($value, $filter['filter_id']);
      
      $this->renderPartial('getDependentParams',array(
          "filter"=>$filter,
          "params" =>  Helpers::simplify($params, "filter_param_id", "name"),
          "paramId" => $selected
      ));
  }

}
