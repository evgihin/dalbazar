<?php

class FilterController extends CAdminController {

    public function actionList($categoryId = 'all') {
        $filter = new Filter();
        $category = new Category();

        $categories = $category->get(NULL, NULL, TRUE);

        $this->act
                ->add('admin/filter/add')
                ->save('filterList')
                ->extendLink('goToCart', array('admin/filter/list', 'categoryId' => 'removed'), 'удаленные', '../images/theme/icon-32-banner-tracks.png');

        if ($categoryId === 'all') {
            $filters = $filter->getFullAll();
            $this->act->delete('filterList');
        } elseif ($categoryId === 'removed') {
            $filters = $filter->getFullRemoved();
            $this->act->removeAction('goToCart');
            $this->act->extendLink('goToAll', array('admin/filter/list', 'categoryId' => 'all'), 'Все фильтры', '../images/theme/icon-32-banner-tracks.png');
            $this->act->undelete('filterList');
        } else {
            $categoryId = (int) $categoryId;
            $filters = $filter->getFullByCategory($categoryId);
            $this->act->delete('filterList');
        }
        
        
        $dependings = $filter->getDependentCategory(Helpers::getIdArray($filters, "filter_id"));
        $dependings = Helpers::groupBy($dependings, "filter_id");
        $allCategories = Helpers::setId($category->get(), "category_id");
        foreach($dependings as &$depending){
            foreach ($depending as &$dep) {
                if (isset($allCategories[$dep['category_id']]))
                    $dep = $allCategories[$dep['category_id']]['name'];
                else
                    unset ($dep); 
            }
        }
        unset($allCategories);
        
        $this->render('list', array(
            'filters' => $filters,
            'categoryId' => $categoryId,
            'categories' => $categories,
            'dependings' => $dependings
        ));
    }

    public function actionDo() {
        $filter = new Filter();
        switch ($_POST['action']) {
            case 'save':
                $filter->updatePos($_POST['pos']);
                break;
            case 'delete':
                $filter->delete($_POST['select']);
                break;
            case 'undelete':
                $filter->undelete($_POST['select']);
                break;
        }
        $this->redirect($_SERVER['HTTP_REFERER']);
        //print_r($_POST);
    }

    public function actionEdit($filterId) {
        $cFilter = new Filter;
        $filterId = (int) $filterId;
        $filters = $this->toAssoc($cFilter->getOnlyType('s'), 'filter_id');
        $params = $cFilter->getAllParam($filterId);
        $this->act
                ->extendJS("addParam", "addParam();", "добавить<br>параметр", "images/theme/icon-32-new.png")
                ->close('admin/filter/list')
                ->apply('filterEdit')
                ->save('filterEdit');

        $categoryXref = $this->toAssoc($cFilter->getDependCategories($filterId), 'category_id');
        $category = new Category();
        $categories = $category->get(NULL, 0);
        $subCategories = $category->get($this->getIdArray($categories, 'category_id'), 1, true);

        if (! $filter = $cFilter->getByFilter($filterId))
            throw new CHttpException(404, "Такого фильтра не найдено");
        else
            $this->render('edit', array(
                'filters' => $filters,
                'id' => $filterId,
                'mainFilter' => $filter,
                'params' => $params,
                'categories' => $categories,
                'subCategories' => $subCategories,
                'categoryXref' => $categoryXref,
            ));
    }

    public function actionAdd() {
        $filter = new Filter;
        //обнуляем значения, чтобы использовать вид editFilter
        $filterId = false;
        $filters = $this->toAssoc($filter->getOnlyType('s'), 'filter_id');
        $mainFilter = array(
            'filter_id' => false,
            'name' => '',
            'type' => 's',
            'from' => '',
            'to' => '',
            'step' => '',
            'depend' => '',
            'top_count' => '10',
            'piece' => '',
            'pos' => '99'
        );
        $params = array();
        $this->act
                ->extendJS("addParam", 'addParam();', "добавить<br>параметр", 'images/theme/icon-32-new.png')
                ->close('admin/filter/list')
                ->apply('filterEdit')
                ->save('filterEdit');

        $categoryXref = array();
        $category = new Category();
        $categories = $category->get(NULL, 0);
        $subCategories = $category->get($this->getIdArray($categories, 'category_id'), 1, true);

        $this->render('edit', array(
            'filters' => $filters,
            'id' => $filterId,
            'mainFilter' => $mainFilter,
            'params' => $params,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'categoryXref' => $categoryXref,
        ));
    }

    //сохранить данные в БД. в пост приходят массивы:
    //oldparam[id] = параметры, которые поменялись
    //newparam[] = новые параметры, которые нужно добавить в таблицу
    //delparam[id] = те, которые нужно удалить из БД
    //pos[id] = позиции и удаляемых и старых элементов, тоже надо сохранить
    public function actionSave($filterId) {
        $filter = new Filter();

        $filterId = (int) $filterId;
        if (!$filterId)
            return;
        //получаем основную информацию о фильтре и обновляем её
        if (!isset($_POST['filter']))
            throw new CHttpException(400, 'В запросе не указаны параметры фильтра');
        $filterData = $_POST['filter'];
        $filter->updateFilter($filterId, $filterData['name'], $filterData['type']);

        if (!isset($_POST['categories']))
            throw new CHttpException(400, 'В запросе не указан список зависимых категорий');
        $filter->setDependCategories($filterId, $_POST['categories']);

        switch ($filterData['type']) {
            case 'i':
                $filter->updateFilterI($filterId, $filterData['from'], $filterData['to'], $filterData['step'], $filterData['piece']);
                break;
            case 's':
                $filter->updateFilterS($filterId, $filterData['depend'], (int) $filterData['top_count']);

                $oldparam = (isset($_POST['oldparam'])) ? $_POST['oldparam'] : array();
                $newparam = (isset($_POST['newparam'])) ? $_POST['newparam'] : array();
                $delparam = (isset($_POST['delparam'])) ? $_POST['delparam'] : array();
                $pos = (isset($_POST['pos'])) ? $_POST['pos'] : array();

                array_pop($newparam); //удалить последний элемент нового параметра

                $filter->updateParamPos($pos);
                $filter->updateParam($oldparam);
                $filter->addParam($newparam, $filterId);
                $filter->removeParam($delparam);
                
                foreach ($newparam as $dummy){
                    Log::admin("param/insert",array("filter_id"=>$filterId), "Создан параметр и добавлен к фильтру");
                }
                foreach ($delparam as $id=>$dummy){
                    Log::admin("param/remove",array("filter_id"=>$filterId,"param_id"=>$id), "Параметр фильтра удален");
                }
                foreach ($oldparam as $id=>$dummy){
                    Log::admin("param/update",array("filter_id"=>$filterId), "Параметр фильтра изменен");
                }

                break;
        }
        
        Log::admin("filter/save",array("filter_id"=>$filterId), "Фильтр обновлен");

        //решаем что делать дальше
        switch ($_POST['action']) {
            case 'save': $this->redirect(array('admin/filter/list'));
                break;
            case 'apply': $this->redirect(
                        array('admin/filter/edit', 'filterId' => $filterId)
                );
                break;
        }
    }

    public function actionInsert() {
        $filterData = $_POST['filter'];
        $filter = new Filter();
        $filterId = $filter->addFilter($filterData['name'], $filterData['type']);

        if (!isset($_POST['categories']))
            throw new CHttpException(400, 'В запросе не указан список зависимых категорий');
        $filter->setDependCategories($filterId, $_POST['categories']);

        switch ($filterData['type']) {
            case 'i':
                $filter->updateFilterI($filterId, $filterData['from'], $filterData['to'], $filterData['step'], $filterData['piece']);
                break;
            case 's':
                $filter->updateFilterS($filterId, $filterData['depend'], (int) $filterData['top_count']);

                $newparam = (isset($_POST['newparam'])) ? $_POST['newparam'] : array();

                array_pop($newparam);
                
                foreach ($newparam as $dummy){
                    Log::admin("param/insert",array("filter_id"=>$filterId), "Создан параметр и добавлен к фильтру");
                }

                $filter->addParam($newparam, $filterId);

                break;
        }
        
        Log::admin("filter/insert",array("filter_id"=>$filterId), "Фильтр создан");

        //решаем что делать дальше
        switch ($_POST['action']) {
            case 'save': $this->redirect(array('admin/filter/list'));
                break;
            case 'apply': $this->redirect(
                        array('admin/filter/edit', 'filterId' => $filterId)
                );
                break;
        }
    }

    public function actionDepend($filterId) {
        $filterId = (int) $filterId;
        if (!$filterId)
            throw new CHttpException('400', 'Неверно указан или не указан ИД фильтра');
        $filter = new Filter();
        $params = $filter->getAllParam($filterId);
        $filterDependId = $filter->getByFilter($filterId)['depend'];
        $dParams = $filter->getAllParam($filterDependId);
        $depends = $this->toAssoc($filter->getDepends($this->getIdArray($params, 'filter_param_id')), 'filter_param_id');

        $this->render('depend', array(
            'params' => $params,
            'dParams' => $dParams,
            'id' => $filterId,
            'dependId' => $filterDependId,
            'depends' => $depends,
        ));
    }

    public function actionSaveDepend($filterId) {

        $filter = new Filter();
        $filter->saveDepends($_POST['depend']);
        
        Log::admin("filter/SaveDepend",array("filter_id"=>$filterId), "Обновлены зависимости для фильтра");

//    решаем что делать дальше
        switch ($_POST['action']) {
            case 'save': $this->redirect(array('admin/filter/list'));
                break;
            case 'apply': $this->redirect(
                        array('admin/filter/depend', 'filterId' => $filterId)
                );
                break;
        }
    }

}
