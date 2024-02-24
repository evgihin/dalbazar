<?php

/**
 * Контроллер расширенный для использования на основной части сайта
 */
class CFrontEndController extends CUserController {

  /**
   * @var string the default layout for the controller view. Defaults to '//layouts/column1',
   * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
   */
  public $layout = '//layouts/main';
  public $left = NULL;
  public $right = NULL;

  /**
   * @var array context menu items. This property will be assigned to {@link CMenu::items}.
   */
  public $menu = array();

  /**
   * @var array the breadcrumbs of the current page. The value of this property will
   * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
   * for more details on how to specify this property.
   */
  public $breadcrumbs = array();

  public $articleRight = "";

  public function __construct($id, $module = null) {
    //if (!YII_DEBUG)
      //Yii::app()->session->regenerateID();
    parent::__construct($id, $module);
  }

  public function toAssoc($array, $fieldId, $fieldId2 = '') {

    $res = array(); //результат операции
    $first = array(); //запоминает, первый раз записал или нет
    foreach ($array as $val) {
      $key = $val[$fieldId]; //ключ массива
      //если раньше значения с таким ключем не существовало - создадим его
      if (!$fieldId2 && !isset($res[$key])) {
        $res[$key] = $val;
        $first[$key] = false;
      }
      //иначе проверяем, если там уже много значений, то просто добавляем. если строка - делаем массив
      else {
        if (isset($first[$key]) && $first[$key] == false) {
          $res[$key] = array($res[$key]);
          $first[$key] = true;
        }
        $res[$key][] = $val;
      }
    }

    if ($fieldId2) {
      foreach ($res as &$val) {
        $val = $this->toAssoc($val, $fieldId2);
      }
    }

    return $res;
  }

  public function getIdArray($array, $fieldId) {
    $ids = array();
    foreach ($array as $c) {
      if (isset($c[$fieldId]))
        $ids[] = $c[$fieldId];
    }
    return $ids;
  }

}