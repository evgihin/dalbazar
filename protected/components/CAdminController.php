<?php

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class CAdminController extends CUserController {

    /**
     * @var string the default layout for the controller view. Defaults to '//layouts/column1',
     * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
     */
    public $layout = '//layouts/adminMain';
    public $defaultAction = 'list';

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
    //левая панель пользователя
    public $left = NULL;
    
    //действия пользователя над элементами. Появляются вверху панели управления и прикрепляются к верху страницы при скроллинге
    /**
     * @var CActionsHelper Генератор действий на панели управления
     */
    public $act = NULL;

    public function __construct($id, $module = null) {

        parent::__construct($id, $module);
        
        //проверяем права доступа
        if (!$this->_CheckPerms()) {
            throw new CHttpException(404,Yii::t('yii','Unable to resolve the request "{route}".',
				array('{route}'=>$id)));
        }
        
        //ставим левую панель
        $this->left = $this->renderPartial('/admin/leftPanel', NULL, true);
        
        $this->act = new CActionsHelper();
    }

    private function _CheckPerms() {
        $action = $this->getAction();
        if (Yii::app()->user->getState('admin_level') ) {
            return true;
        } else {
            return false;
        }
    }

    public function toAssoc($array, $fieldId) {
        $res = array();
        foreach ($array as $val) {
            //если раньше значения с таким ключем не существовало - создадим его
            if (!isset($res[$val[$fieldId]]))
                $res[$val[$fieldId]] = $val;
            //иначе проверяем, если там уже много значений, то просто добавляем. если строка - делаем массив
            else {
                if (!is_array($res[$val[$fieldId]]))
                    $res[$val[$fieldId]] = array($res[$val[$fieldId]]);
                $res[$val[$fieldId]][] = $val;
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

    public function actionError() {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('/site/error', $error);
        }
    }

}
