<?php

class FarpostController extends CAdminController {

    var $defaultAction = 'index';

    public function actionIndex() {
        //$this->act = array('add'=>'admin/user/add','save'=>'admin/user/save','apply'=>'admin/user/save');
        $this->render('index');
        //echo 'asd';
    }

    public function actionCleanParsed() {
        $cFarpost = new Farpost();
        $count = $cFarpost->cleanParsed();
        Log::admin("farpost/CleanParsed",array("count"=>$count), "Все отпарсенные объявления удалены");
        $this->redirect(Yii::app()->request->urlReferrer);
    }

    public function actionCategoryTree($farpost_category_id = NULL) {
        $cFarpost = new Farpost();
        $categories = $cFarpost->getCategoryList();
        $categories = Helpers::toAssoc($categories, "farpost_category_parent_id");
        $cCategory = new Category();
        $sourceCategories = Helpers::toAssoc($cCategory->get(), "category_parent_id", "category_id");
        $this->render("categoryList", array(
            "categories" => $categories,
            "sourceCategories" => $sourceCategories,
            "relations" => Helpers::toAssoc($cFarpost->getRelations(), "farpost_category_id"),
            "farpostCategoryId" => $farpost_category_id
        ));
    }

    public function actionSaveCategoryTree() {
        $changedIds = $_POST['changes'];
        $depends = $_POST['depend'];
        $cFarpost = new Farpost();

        $arr = array();
        foreach ($changedIds as $id) {
            $arr[$id] = $depends[$id];
        }
        $cFarpost->setRelations($arr);
        Log::admin("farpost/saveCategoryTree",array("changed_id"=>$changedIds), "Обновлены связи категорий farpost для ".  count($changedIds)." категорий");
        $this->redirect(array("admin/farpost/categorytree"));
    }

    public function actionAddParseTask() {
        $cFarpost = new Farpost();
        $categories = $cFarpost->getCategoryList();
        $categories = Helpers::toAssoc($categories, "farpost_category_parent_id");
        $this->render("addParseTask", array('categories' => $categories));
    }

    /**
     * список отпарсенных объявлений
     */
    public function actionListparseadvert($farpost_category_id=NULL) {
        if (!is_null($farpost_category_id))
            Yii::app()->user->setState(md5("farpost_cateogry_lpa"), $farpost_category_id); //запоминаем выбор select'а
        else {
            $farpost_category_id = Yii::app()->user->getState(md5("farpost_cateogry_lpa"), NULL);
        }
        $cFarpost = new Farpost();
        $count = $cFarpost->getParseAdvertCount($farpost_category_id);
        $pages = new CPagination($count);
        $pages->pageSize = 10;
        $pages->applyLimit($cFarpost);
        $adverts = $cFarpost->getParseAdvert($farpost_category_id);
        
        $this->render('listParseAdvert', array(
            'adverts' => $adverts,
            'count' => $count,
            'pages' => $pages,
            'farpostCategories' => $cFarpost->getDropDownListCategory(),
            'farpostCategoryId' => $farpost_category_id
        ));
    }

    /**
     * Меню редактирования отпарсенных объявлений
     * @param int $farpost_parsed_id
     */
    public function actionEditParseAdvert($farpost_parsed_id, $farpost_category_id=0) {
        $nextCategoryId = (int)$farpost_category_id;
        $cFarpost = new Farpost();

        $oldAdvert = $cFarpost->getParseAdvertById($farpost_parsed_id);

        if (!$oldAdvert)
            throw new CHttpException(400, "Нет такого объявления.");
        
        $nextId = $cFarpost->getNextParseAdvertId($nextCategoryId, $farpost_parsed_id); //получаем ИД следующего объявления для кнопки "далее"

        $oldAdvert = $cFarpost->normalize($oldAdvert); //привели в божеский вид
        $newAdvert = $cFarpost->classifyAdvert($oldAdvert); //попытались запарсить объявления

        //получаем категорию и подкатегорию
        $farpostCategory = $cFarpost->getCategoryById($oldAdvert['farpost_category_id']);
        if ($farpostCategory['farpost_category_parent_id'])
            $farpostSubCategory = $cFarpost->getCategoryById($farpostCategory['farpost_category_parent_id']);
        else
            $farpostSubCategory = NULL;

        //получаем города
        $cCity = new City();
        $cities = $cCity->getAll();
        
        //получаем фильтры
        $cFilter = new Filter();
        $filters = Helpers::setId($cFilter->get("filter","removed=0"), "filter_id");
        
        //получаем связи фильтров с категориями
        $filterRelations = Helpers::groupAndSimplify($cFilter->getRelations(), "category_id", "filter_id");
        
        //получаем параметры
        $cParam = new Param();
        $params = Helpers::groupBy($cParam->get("filter_param"),"filter_id");
        foreach ($params as &$param){
            $param = Helpers::simplify($param, "filter_param_id", "name");
        }
        
        
        $this->render('editParseAdvert', array(
            'oldAdvert' => $oldAdvert,
            'newAdvert' => $newAdvert,
            'farpostCategory' => $farpostCategory,
            'farpostSubCategory' => $farpostSubCategory,
            'id' => $farpost_parsed_id,
            'nextId' => $nextId,
            'nextCategoryId' => $nextCategoryId,
            'cities' => $cities,
            'filters' => $filters,
            'params' => &$params,
            'filterRelations' => $filterRelations,
        ));
    }

    //сохраняем отпарсенное объявление в БД
    public function actionSaveParseAdvert($farpost_parsed_id, $next=0, $farpost_category_id=0) {
        if ($_POST['action']=="delete")
            return $this->actionDeleteParseAdvert ($farpost_parsed_id, $next, $farpost_category_id);
        
        $cFarpost = new Farpost();
        $oldAdvert = $cFarpost->getParseAdvertById($farpost_parsed_id);
        $oldAdvert = $cFarpost->normalize($oldAdvert);
        $newAdvert = $cFarpost->classifyAdvert($oldAdvert);
        
        $advert = new Advert("addParser");
        $advert->attributes = $_POST;
        $advert->provider = "farpost";
        $advert->create_time = $newAdvert['create_time'];
        $advert->expirate_time = $newAdvert['expirate_time'];
        $advert->link = $newAdvert['source_link'];
        
        //добавляем объявление
        if (!$advert->validate()){
            $this->model = $advert;
            return $this->actionEditParseAdvert($farpost_parsed_id);
        }
        $advertId = $advert->insert();
        
        //добавляем фильтры
        $cFilter = new Filter();
        $cFilter->setValues($advertId, $_POST['filter']);
        
        //добавляем картинки
        $cImage = new Image();
        for ($i=0;$i<count($oldAdvert['images_big']);$i++){
            $cImage->addRemote($advertId, $oldAdvert['images'][$i], $oldAdvert['images_big'][$i], $i==0);
        }
        
        //учим farpost работе с фильтрами
        $cFilter = new Filter();
        if (!isset($_POST['filter']) || !is_array($_POST['filter']))
            $_POST['filter'] = array();
        
        $filters = $cFilter->getByCategory($_POST['category']);
        foreach ($filters as $filter){
            if ($filter['type']=='s' && isset($_POST['filter'][$filter['filter_id']])){
                $cFarpost->learnFilter($farpost_parsed_id, $filter['filter_id'], $_POST['filter'][$filter['filter_id']]);
            }
        }
        
        //удаляем farpost объявление
        $cFarpost->removeParseAdvert($farpost_parsed_id);
        Log::admin("farpost/SaveParseAdvert",array("advert_id"=>$advertId,"farpost_category_id"=>$farpost_category_id), "Отпарсенное объявление добавлено на сайт");
        
        //выходим
        if ($next){
            $this->redirect(array('admin/farpost/editParseAdvert', "farpost_parsed_id" => $next, "farpost_category_id"=>$farpost_category_id));
        } else {
            $this->redirect(array("admin/farpost/listParseAdvert"));
        }
    }
    
    public function actionDeleteParseAdvert($farpost_parsed_id, $next=0, $farpost_category_id=0){
        $cFarpost = new Farpost;
        
        //удаляем farpost объявление
        $cFarpost->removeParseAdvert($farpost_parsed_id);
        
        
        Log::admin("farpost/DeleteParseAdvert",array("farpost_parsed_id"=>$farpost_parsed_id,"farpost_category_id" => $farpost_category_id), "Отпарсенное объявление удалено");
        
        //выходим
        if ($next){
            $this->redirect(array('admin/farpost/editParseAdvert', "farpost_parsed_id" => $next, "farpost_category_id"=>$farpost_category_id));
        } else {
            $this->redirect(array("admin/farpost/listParseAdvert"));
        }
    }

    public function actionError() {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }

    public function actionFillFilterBase($farpost_category_id) {
        $cFarpost = new Farpost();
        $adverts = $cFarpost->getParseAdvertByCategoryId($farpost_category_id);
        $i = 0;
        foreach ($adverts as $advert) {
            $advert = $cFarpost->normalize($advert);
            foreach ($advert['params'] as $id => $val) {
                if ($cFarpost->addEmptyFilter($id, $advert['farpost_category_id']))
                    $i++;
            }
        }
        Log::admin("farpost/FillFilterBase",array("farpost_category_id" => $farpost_category_id, "count"=>$i), "Категория заполнена фильтрами на основе отпарсенных объявлений");
        $this->render('fillFilterResult', array(
            "num" => $i,
            'farpostCategoryId' => $farpost_category_id
        ));
    }

    public function actionFilters($farpost_category_id = '') {
        //если не указан ИД категории, выдаем список категорий
        $cFarpost = new Farpost();
        if (!$farpost_category_id) {
            $categories = $cFarpost->getCategoryList();
            $categories = Helpers::toAssoc($categories, "farpost_category_parent_id");
            
            $cCategory = new Category();
            $sourceCategories = Helpers::setId($cCategory->get(), "category_id");
            
            $this->render("filterConnectSelectCategory", array(
                "categories" => $categories,
                "sourceCategories" => $sourceCategories,
                "relations" => Helpers::toAssoc($cFarpost->getRelations(), "farpost_category_id"),
                "advertCount" => $cFarpost->getAdvertCountByCategory(),
                "filterCount" => $cFarpost->getFilterCountByCategory()
            ));
        } else {
            $farpostCategory = $cFarpost->getCategoryById($farpost_category_id);
            if (!$farpostCategory)
                throw new CHttpException(400, "Неверно указана категория");
            $farpostFilters = $cFarpost->get('farpost_filter_relation', "farpost_category_id=?", array($farpost_category_id));
            $count = $cFarpost->getAdvertCountByCategory($farpost_category_id);
            if (isset($count[$farpost_category_id]))
                $count = $count[$farpost_category_id];
            else
                $count = 0;
            $cFilter = new Filter();
            
            if (!($origCategoryId = $cFarpost->getRealCategoryId($farpost_category_id)))
                    throw new CHttpException(400, "Категория не связана с основной. Редактирование фильтров невозможно");
            
            $originalFilters = $cFilter->getByCategory($origCategoryId);
            $originalFilters = Helpers::simplify($originalFilters, "filter_id", "name");
            $this->render("filterConnect", array(
                "category" => $farpostCategory,
                "farpostFilters" => $farpostFilters,
                'farpostCategoryId' => $farpost_category_id,
                'advertCount' => $count,
                "originalFilters" => $originalFilters
            ));
        }
    }
    
    public function actionSaveFilters($farpost_category_id){
        $cFarpost = new Farpost();
        $farpostCategory = $cFarpost->getCategoryById($farpost_category_id);
        if (!$farpostCategory)
            throw new CHttpException(400,"Категория не найдена");
        Helpers::required($_POST, array("depending"));
        $newDependings = $_POST['depending'];
        $oldDependings = $farpostFilters = $cFarpost->get('farpost_filter_relation', "farpost_category_id=?", array($farpost_category_id));
        foreach($oldDependings as $oldDepending){
            if (is_null($oldDepending['filter_id']))
                $oldDepending['filter_id'] = 0;
            //если значение изменилось
            if (isset($newDependings[$oldDepending['farpost_filter_relation_id']]) && $oldDepending['filter_id'] != $newDependings[$oldDepending['farpost_filter_relation_id']]){
                $cFarpost->clearParams($oldDepending['farpost_filter_relation_id']);
                $cFarpost->setFilterRelation($oldDepending['farpost_filter_relation_id'], $newDependings[$oldDepending['farpost_filter_relation_id']]);
            }
        }
        Log::admin("farpost/SaveFilters",array("farpost_category_id" => $farpost_category_id), "Обновлены зависимости фильтров категории");
        $this->redirect(array("admin/farpost/filters"));
    }
    
    /**
     * Список задач для парсинга
     * @param string $show Тип отображения. Может быть all, active, archive
     */
    public function actionListParseTask($show='all'){
        $pages = new CPagination(10000);
        $pages->setPageSize(50);
        
        $cParser = new Parser();
        $pages->applyLimit($cParser);
        switch ($show) {
            case "active":
                $tasks = $cParser->get("parse_task","end_time = 0 OR end_time = NULL", array(), true);
                break;
            case "archive":
                $tasks = $cParser->get("parse_task","end_time > 0", array(), true);
                break;

            case "all": default:
                $tasks = $cParser->get("parse_task", NULL, array(), true);
                break;
        }
        
        $pages->setItemCount($cParser->count());
        
        $this->render("tasks",array(
           "tasks" => $tasks,
            "pages" => $pages,
            "show" => $show
        ));
    }

}
