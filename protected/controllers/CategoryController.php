<?php

class CategoryController extends CFrontEndController {

    public $left;

    public function actionShow() {
        //throw new CHttpException(500, "<h1>Функция пока недоступна</h1>"); return;
        $catModel = new Category();
        $filter = new Filter();
        $param = new Param();
        $advert = new Advert();

        //получили ИД категории из данных запроса
        $id = $catModel->idFromRequest();
        if (!Helpers::checkId($id))
            throw new CHttpException(400, 'Не указан либо неверно указан алиас/ид категории');
        $category = $catModel->getByCategory($id);

        //сохраняем информацию о посещении
        $catModel->detectUse($id);

        if (!Yii::app()->request->isAjaxRequest) {
            //получаем фильтры для выбранной категории и их параметры
            $filters = $this->toAssoc($filter->getByCategory($id), 'filter_id');
            $params = $param->getByFilter(Helpers::getIdArray($filters, 'filter_id'));
            $params = $this->toAssoc($params, 'filter_id');

            //рендерим левую панель
            $this->left = $this->renderPartial('left', array(
                'filters' => $filters,
                'params' => $params,
                'id' => $id,
                    ), true);
        }

        //задаем фильтры
        if (!empty($_POST['filters']))
            $advert->setFilterS(Filter::validateSFromQuery($_POST['filters']));
        if (!empty($_POST['filteri']))
            $advert->setFilterIFromQuery($_POST['filteri']);

        //инициализируем постраничную навигацию

        $page = new CPagination(1000);
        if (isset($_POST['pagesize']) && in_array($_POST['pagesize'], Yii::app()->params['pageSizeValues'])) {
            Yii::app()->user->setState('catPageSize', $_POST['pagesize']);
        }
        $pageSize = Yii::app()->user->getState('catPageSize', 10);
        $page->setPageSize($pageSize);

        //применяем постраничную навигацию к запросу
        $page->applyLimit($advert);

        //получаем список объявлений с учетом установленных фильтров
        $adverts = $advert->getByCategory($id, TRUE);
        
        if ($advert->count()==0){
            if (Yii::app()->request->isAjaxRequest){
                echo CJSON::encode (array("data"=>$this->renderPartial("empty")));
                return;
            }
            else 
                return $this->render("empty");
        }

        $page->setItemCount($advert->count());


        $advertId = $this->getIdArray($adverts, 'advert_id'); //список ИД-ов объявлений
        
        //получаем список картинок
        $cImage = new Image();
        $images = Helpers::groupBy($cImage->getByAdvertFull($advertId), "advert_id");
        
        //получаем значения фильтров, упорядоченных по ИД-у объявления
        $values = Helpers::groupBy($param->getValues($advertId), 'advert_id', 'filter_id');

        $attachedIds = $advert->getIdAttachedByCategory($id);
        $attachedAdverts = $advert->getByAdvert($attachedIds);
        $attachedValues = Helpers::groupBy($param->getValues($attachedIds), 'advert_id', 'filter_id');

        $feature = new Feature($advertId);

        $renderParams = array(
            'adverts' => $adverts,
            'images' => $images,
            'valuesAll' => $values,
            'category' => $category,
            'attached' => $attachedAdverts,
            'attachedValues' => $attachedValues,
            'page' => $page,
            'features' => $feature
        );


        //получаем объявления и отдаём в вид
        if (Yii::app()->request->isAjaxRequest)
            echo CJSON::encode(array(
                'data' => $this->renderPartial(($category['flypage']) ? 'fly_' . $category['flypage'] : 'fly_default', $renderParams, true),
            ));
        else
            $this->render(($category['flypage']) ? 'fly_' . $category['flypage'] : 'fly_default', $renderParams);
    }

    public function actionFilter($categoryId) {
        $catModel = new Category();
        $filter = new Filter();
        $param = new Param();
        $advert = new Advert();
        $jResponse = array(); //сюда собираются данные для json ответа

        $category = $catModel->getByCategory($categoryId); //информация о категории, которую просматриваем
        if (Yii::app()->request->isAjaxRequest) {

            if (isset($_POST['filters']))
                $advert->setFilterSFromQuery($_POST['filters']);
            if (isset($_POST['filteri']))
                $advert->setFilterIFromQuery($_POST['filteri']);

            //проверяем включены ли расширенные фильтры. если да - то добавить в ответ сервера и фильтр со включенными параметрами
            $filterEId = false;
            if (isset($_POST['filterE']) && isset($_POST['filterEId']))
                $advert->setFilterSFromQuery(array($_POST['filterEId'] => $_POST['filterE']));

            $filterS = $advert->getFilterS();
            $filterI = $advert->getFilterI();

            if (isset($_POST['filterE']) && isset($_POST['filterEId'])) {
                //собираем данные для обновления фильтра
                $jResponse['update'] = array(
                    'id' => $filterEId,
                    'html' => $this->renderPartial('filtere_s', array(
                        'filter' => $filter->getByFilter($filterEId),
                        'params' => $filter->getParamByParam($filterS)
                            ), TRUE)
                );
            }








            //получаем сами объявления
            $adverts = $advert->getByCategory($categoryId);

            if (!$adverts)
                $jResponse['data'] = $this->renderPartial('empty', NULL, TRUE);
            else {
                $image = new Image();
                $images = $image->getByAdvert($this->getIdArray($adverts, 'advert_id'));
                $advertId = $this->getIdArray($adverts, 'advert_id');
                $valuesI = $this->toAssoc($param->getValues($advertId, 'i'), 'advert_id', 'filter_id');
                $valuesS = $this->toAssoc($param->getValues($advertId, 's'), 'advert_id', 'filter_id');


                $jResponse['data'] = $this->renderPartial(($category['flypage']) ? 'fly_' . $category['flypage'] : 'fly_default', array(
                    'adverts' => $adverts,
                    'imagesAll' => $images,
                    'valuesIAll' => $valuesI,
                    'valuesSAll' => $valuesS,
                        ), true);
            }

            echo CJSON::encode($jResponse);
        }
    }

    /**
     * Выдает пользователю список "всех" параметров для указанного фильтра
     * @param int $filter_id
     * @throws CHttpException
     */
    public function actionAllParams($filter_id) {
        if (!Helpers::checkId($filter_id))
            throw new CHttpException(400, 'Неверно заданы параметры запроса');
        $filterId = (int) $filter_id;
        unset($filter_id);

        $filter = new Filter();
        $param = new Param();
        $filter_info = $filter->getByFilter($filterId); //получаем инфу о фильтре
        if (!$filter_info)
            throw new CHttpException(400, "Такого ИД-а не существует в базе");

        $paramIds = array(); //получаем предустановленные параметры
        if (Helpers::required($_POST, 'filters', false))
            $paramIds = Filter::validateSFromQuery($_POST['filters']);
        //собираем набор параметров
        if ($filter_info['depend']) { //если фильтр зависимый, то необходимо получать только зависимые параметры
            $params = $param->getByFilterOnlyDepend($filterId, $paramIds);
        } else { //иначе получаем все параметры
            $params = $param->getByFilter($filterId);
        }

        if (!$params) {
            echo "У этого фильтра нет параметров, либо он зависит от фильтра, параметры которого не заданы";
            return;
        }

        $this->renderPartial('allParams', array(
            'filter' => $filter_info,
            'params' => $params,
            'checked' => $paramIds,
            'id' => $filterId,
        ));
    }

    /**
     * получает список установленнных параметров для определенного фильтра и отдает код для выдачи в фильре
     * @throws CHttpException
     */
    public function actionSetAllParams() {
        if (!Helpers::required($_POST, 'filterEId', false) || !Helpers::checkId($filterId = $_POST['filterEId']))
            throw new CHttpException(400, 'Не указан ИД фильтра');

        if (!empty($_POST['filterE'])) { //если задан хоть 1 фильтр, то получаем их данные и выдаем в форму
            $paramIds = Filter::validateSFromQuery($_POST['filterE']);
            if (!empty($paramIds)) {
                $filter = new Filter();
                $param = new Param();
                $filterHTML = $this->renderPartial('filter_s', array(
                    'filter' => $filter->getByFilter($filterId),
                    'params' => $param->getByParam($paramIds),
                    'checked' => $paramIds,
                    'checkDepend' => false,
                    'showExtBtn' => true,
                        ), true);
                echo CJSON::encode(array('update' => array('id' => $filterId, 'html' => $filterHTML)));
            }
        }
    }

    /**
     * Обновляет и выдает JSON-код зависимых параметров на основе JSON-формы
     */
    public function actionUpdateDependParams() {
        //проверяем наличие установленных фильтров
        if (!Helpers::required($_POST, 'filters', false))
            return;

        $param = new Param();
        $filter = new Filter();

        $checkedParams = Filter::validateSFromQuery($_POST['filters']); //параметры, которые были отмечены галочкой
        //получаем все зависимые параметры
        $depParams = $param->getParamByDependParam($checkedParams);
        //сортируем их по ИД-у фильтра
        $depParams = Helpers::toAssoc($depParams, 'filter_id'); //тут вылезет баг если для одного из фильтров придет всего 1 параметр
        //@todo переделать функцию toAssoc

        $filterIds = array_keys($depParams); //получаем ИД-ы зависящих фильтров

        $filters = $filter->getByFilter($filterIds);

        $jResponse['depend'] = array(); //сюда собираем зависимые коды
        if ($filters)
            foreach ($filters as $depFilter) {
                if (isset($depParams[$depFilter['filter_id']])) {
                    $jResponse['depend'][$depFilter['filter_id']] = $this->renderPartial(
                            'filter_s', array(
                        'filter' => $depFilter,
                        'params' => $depParams[$depFilter['filter_id']],
                        'checkDepend' => false,
                        'checked' => $checkedParams,
                            ), TRUE);
                } else {
                    $jResponse['depend'][$depFilter['filter_id']] = $this->renderPartial(
                            'filter_s', array(
                        'filter' => array('depend' => true)
                            ), TRUE);
                }
            }
        echo CJSON::encode($jResponse);
    }

}