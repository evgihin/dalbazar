<?php

class Farpost extends CExtFormModel {

    function cleanParsed() {
        return Yii::app()->db->createCommand()->delete("farpost_parsed");
    }
    
    function removeParseAdvert($id){
        return Yii::app()->db->createCommand()->delete("farpost_parsed", "farpost_parsed_id=:fpid", array(":fpid"=>$id));
    }

    function getCategoryList($parent = Null) {
        $q = Yii::app()->db->createCommand()
                ->select()
                ->from("farpost_category")
                ->order("farpost_category_parent_id ASC, pos ASC");

        if (!is_null($parent)) {
            $q->where_and("farpost_category_parent_id=:pid", array(":pid" => $parent));
        }
        return $q->queryAll();
    }

    /**
     * Получить данные в формате одномерного массива, где список категорий
     * @param type $catalogId
     * @return type
     */
    public function getDropDownListCategory() {
        //получаем список категорий
        $categories = Helpers::groupBy($this->getCategoryList(), "farpost_category_parent_id");
        $advertCounts = $this->getAdvertCountByCategory();
        $res = array();
        foreach ($categories[0] as $category) {
            if (isset($categories[$category['farpost_category_id']])) {
                foreach ($categories[$category['farpost_category_id']] as &$subCategory) {
                    if (!isset($advertCounts[$subCategory['farpost_category_id']]))
                        $advertCounts[$subCategory['farpost_category_id']] = 0;
                    $subCategory['nameCount'] = $subCategory['name'] . " (" . $advertCounts[$subCategory['farpost_category_id']] . ")";
                }
                $res[$category['name']] = Helpers::simplify($categories[$category['farpost_category_id']], "farpost_category_id", "nameCount");
            } else {
                $res[$category['name']] = array();
            }
        }
        return $res;
    }

    function getCategoryById($id) {
        return Yii::app()->db->createCommand()
                        ->select('*')
                        ->from('farpost_category')
                        ->where('farpost_category_id=?', array($id))
                        ->queryRow();
    }

    function getAdvertCountByCategory($farpostCategoryId = NULL) {
        $comm = $this->createCommand()
                ->select("farpost_category_id, COUNT(*) as num")
                ->from("farpost_parsed")
                ->group("farpost_category_id");
        if (!is_array($farpostCategoryId) && !is_null($farpostCategoryId))
            $farpostCategoryId = array($farpostCategoryId);
        if ($farpostCategoryId)
            $comm->where(array("IN", 'farpost_category_id', $farpostCategoryId));

        $res = $comm->query();
        $ret = array();
        foreach ($res as $row) {
            $ret[$row['farpost_category_id']] = $row['num'];
        }
        return $ret;
    }

    function getFilterCountByCategory($farpostCategoryId = NULL) {
        $comm = $this->createCommand()
                ->select("farpost_category_id, COUNT(*) as num")
                ->from("farpost_filter_relation")
                ->group("farpost_category_id");
        if (!is_array($farpostCategoryId) && !is_null($farpostCategoryId))
            $farpostCategoryId = array($farpostCategoryId);
        if ($farpostCategoryId)
            $comm->where(array("IN", 'farpost_category_id', $farpostCategoryId));

        $res = $comm->query();
        $ret = array();
        foreach ($res as $row) {
            $ret[$row['farpost_category_id']] = $row['num'];
        }
        return $ret;
    }

    /**
     * Устанавливает зависимости между категориями сайта и категориями FarPost
     * @param array $depends массив зависимостей в формате array(farpost_category_id=>category_id);
     */
    function setRelations($depends) {
        $ids = array_keys($depends);
        Yii::app()->db->createCommand()->delete("farpost_category_relation", array("in", "farpost_category_id", $ids));

        foreach ($depends as $id => $val) {
            Yii::app()->db->createCommand()->insert("farpost_category_relation", array(
                "farpost_category_id" => $id,
                "category_id" => $val
            ));
        }
    }

    function getRelations() {
        return Yii::app()->db->createCommand()->select()->from("farpost_category_relation")->queryAll();
    }

    /**
     * Добавляет во временное хранилище только что спарсенные объявления
     * @param array $data Массив, полученный из запроса
     */
    function addParseAdvert($data) {
        Yii::app()->db->createCommand()
                ->insert("farpost_parsed", array(
                    "data" => serialize($data),
                    "time" => time(),
                    "farpost_category_id" => (isset($data['farpost_category_id'])) ? $data['farpost_category_id'] : NULL
        ));
    }

    function getParseAdvert($farpostCategoryId = NULL) {
        $q = $this->createCommand()
                ->select()
                ->from("farpost_parsed");
        if ($farpostCategoryId)
            $q->where("farpost_category_id=:fcid", array(":fcid" => $farpostCategoryId));
        return $q->queryAll();
    }

    function getParseAdvertCount($farpostCategoryId = NULL) {
        $comm = Yii::app()->db->createCommand()
                ->select("COUNT(*)")
                ->from('farpost_parsed');
        if (!is_null($farpostCategoryId))
            $comm->where("farpost_category_id=?", array($farpostCategoryId));
        return $comm->queryScalar();
    }

    function getParseAdvertById($id) {
        return $this->createCommand()
                        ->select()
                        ->from("farpost_parsed")
                        ->where("farpost_parsed_id=?", array($id))
                        ->queryRow();
    }

    function getParseAdvertByCategoryId($farpostCategoryId) {
        return $this->createCommand()
                        ->select()
                        ->from("farpost_parsed")
                        ->where("farpost_category_id=?", array($farpostCategoryId))
                        ->queryAll();
    }

    function getNextParseAdvertId($categoryId, $id) {
        return Yii::app()->db->createCommand()
                        ->select("farpost_parsed_id")
                        ->from("farpost_parsed")
                        ->where("farpost_category_id=:fcid AND farpost_parsed_id>:fpid", array(":fcid"=>$categoryId,":fpid"=>$id))
                        ->limit(1)
                        ->queryScalar();
    }

    /**
     * Получает ИД основной категории, с которой связана категория Farpost
     * @param type $farpost_category_id
     * @return type
     */
    function getRealCategoryId($farpost_category_id) {
        return Yii::app()->db->createCommand()
                        ->select("category_id")
                        ->from("farpost_category_relation")
                        ->where("farpost_category_id=?", array($farpost_category_id))
                        ->queryScalar();
    }

    /**
     * Парсит дату формата Фарпоста. Возвращает timestamp даты, либо false в случае ошибочной даты
     * @param string $date Дата формата FarPost
     * @return int timestamp представленной даты
     */
    function parse_date($date, $time = NULL) {
        if (is_null($time))
            $time = time();
        //сперва ищем время
        preg_match_all("~(\d{1,2}):(\d{1,2})~usm", $date, $matches);
        if (empty($matches[0]))
            $hour = $minute = 0;
        else {
            $hour = (int) $matches[1][0];
            $minute = (int) $matches[2][0];
        }

        preg_match_all("~((\d{1,2})\s)?([а-яА-Я]{1,30})(\s(\d{4}))?~usm", $date, $matches);

        if (!empty($matches[0])) {
            $day = 0;

            switch (mb_strtolower($matches[3][0])) {
                case 'сегодня':
                    $day = (int) date('d', $time);
                    $month = (int) date('m', $time);
                    break;
                case 'вчера':
                    $day = (int) date('d', $time - 86400);
                    $month = (int) date('m', $time - 86400);
                    break;
                case 'В начале эпохи ))':
                    $day = (int) date('d', 0);
                    $month = (int) date('m', 0);
                    break;
                case 'январь': $month = 1;
                    break;
                case 'января': $month = 1;
                    break;
                case 'февраль': $month = 2;
                    break;
                case 'февраля': $month = 2;
                    break;
                case 'март': $month = 3;
                    break;
                case 'марта': $month = 3;
                    break;
                case 'апрель': $month = 4;
                    break;
                case 'апреля': $month = 4;
                    break;
                case 'май': $month = 5;
                    break;
                case 'мая': $month = 5;
                    break;
                case 'июнь': $month = 6;
                    break;
                case 'июня': $month = 6;
                    break;
                case 'июль': $month = 7;
                    break;
                case 'июля': $month = 7;
                    break;
                case 'август': $month = 8;
                    break;
                case 'августа': $month = 8;
                    break;
                case 'сентябрь': $month = 9;
                    break;
                case 'сентября': $month = 9;
                    break;
                case 'октябрь': $month = 10;
                    break;
                case 'октября': $month = 10;
                    break;
                case 'ноябрь': $month = 11;
                    break;
                case 'ноября': $month = 11;
                    break;
                case 'декабрь': $month = 12;
                    break;
                case 'декабря': $month = 12;
                    break;
            }

            if (!$day)
                $day = (int) $matches[2][0];

            if (!empty($matches[5][0]))
                $year = (int) $matches[5][0];
            else
                $year = date("Y", $time);

            return mktime($hour, $minute, 0, $month, $day, $year);
        } else
            return false;
    }

    function parse_expirate_date($date, $time = null) {
        $date = explode("ещё", $date);

        if (count($date) != 2)
            return false;

        $date[0] = trim($date[0]);
        $date[1] = trim($date[1]);
        $startTime = $this->parse_date($date[0], $time);
        if (!$startTime)
            return false;

        //года
        preg_match_all("~(\d{1,2})\s([гл])~usm", $date[1], $matches);
        if (!empty($matches[1]))
            $startTime += (int) $matches[1][0] * 86400 * 365;

        //месяцы
        preg_match_all("~(\d{1,2})\s(мес)~usm", $date[1], $matches);
        if (!empty($matches[1]))
            $startTime += (int) $matches[1][0] * 86400 * 30;

        //дни
        preg_match_all("~(\d{1,2})\s(д)~usm", $date[1], $matches);
        if (!empty($matches[1]))
            $startTime += (int) $matches[1][0] * 86400;

        //часы
        preg_match_all("~(\d{1,2})\s(ча)~usm", $date[1], $matches);
        if (!empty($matches[1]))
            $startTime += (int) $matches[1][0] * 3600;

        //минуты
        preg_match_all("~(\d{1,2})\s(мин)~usm", $date[1], $matches);
        if (!empty($matches[1]))
            $startTime += (int) $matches[1][0] * 60;

        //секунды
        preg_match_all("~(\d{1,2})\s(се)~usm", $date[1], $matches);
        if (!empty($matches[1]))
            $startTime += (int) $matches[1][0];

        return $startTime;
    }

    /**
     * Пытается сопоставить фильтры реальным названиям.
     * @param string $id ИД оригинального фильтра на farpost
     * @param string $val Значение оригинального фильтра FarPost
     * @param int $farpostCategoryId ИД категории FarPost
     * @return mixed  Возвращает array(id,val) если получилось, иначе false
     */
    function filtrate($id, $val, $farpostCategoryId) {

        //для начала пытаемся найти фильтр в базе соответствия с функциями
        $filterName = Yii::app()->db->createCommand()->select("function")
                ->from("farpost_filter")
                ->where(array('and', 'name LIKE ?', 'farpost_category_id=?'), array("%" . $id . "%", $farpostCategoryId))
                ->queryScalar();
        if ($filterName) {
            //делаем первую букву большой
            $filterName = 'filter' . mb_strtoupper(mb_substr($filterName, 0, 1)) .
                    mb_substr($filterName, 1, mb_strlen($filterName)); //делаем первую букву заглавной
            return $this->$filterName($val);
        } else {
            //если не нашли, надо поискать по текстовому совпадению с существующими результатами
            $data = Yii::app()->db->createCommand()
                    ->select("filter_id,farpost_filter_relation_id")
                    ->from("farpost_filter_relation")
                    ->where("name LIKE ? AND farpost_category_id=?", array($id, $farpostCategoryId))
                    ->queryRow();
            if (!$data)
                return false;
            else {
                $filterId = $data['filter_id'];
                $paramId = Yii::app()->db->createCommand()
                        ->select("filter_param_id")
                        ->from("farpost_param_relation")
                        ->where("name LIKE ? AND farpost_filter_relation_id=?", array($val, $data['farpost_filter_relation_id']))
                        ->queryScalar();
                if ($paramId)
                    return array("id" => $filterId, "val" => $paramId);
                else
                    return false;
            }
        }
    }

    /**
     * Попытаться классифицировать объявление. Другими словами, преобразовывает его в "божеский" вид
     * @param type $advert
     */
    function classifyAdvert($advert) {
        $ret = array();

        //получаем город
        $cCity = new City();
        $ret['city'] = $cCity->getByName($advert['city']);

        //текст
        $ret['text'] = preg_replace("/([\n\r\s\t]*<br>[\n\r\s\t]*)+/", "\n", $advert['text']); //сперва чикаем лишние пробелы
        $ret['text'] = strip_tags($ret['text']); //затем чикаем все тэги
        $ret['text'] = preg_replace("/[\t]/", "", $ret['text']); //удаляем все табы
        //цена
        $ret['price'] = (int) $advert['price'];

        //заголовок и картинки
        $ret['title'] = $advert['title'];
        $ret['images'] = $advert['images'];
        $ret['images_big'] = $advert['images_big'];

        //время
        $ret['time'] = $advert['time'];
        if (!$ret['create_time'] = $this->parse_date($advert['create_time'], $advert['time']))
            $ret['create_time'] = $advert['time'];
        if (!$ret['expirate_time'] = $this->parse_expirate_date($advert['expirate_time'], $advert['time']))
            $ret['expirate_time'] = $advert['time'] + Yii::app()->params['advertLifeTime'];

        //
        $ret['source_link'] = $advert['source_link'];
        $ret['source_id'] = (int) $advert['source_id'];
        $ret['source_provider'] = 'farpost';
        
        //контактные данные
        $ret['contacts'] =  strip_tags($advert['contacts']);
        $ret['contacts'] =  preg_replace("/[\n\t\r]*\s*\n+[\n\t\r]*/", "\n", $ret['contacts']);
        $ret['contacts'] = preg_replace("/Задать вопрос/ui","",$ret['contacts']);
        $ret['contacts'] =  trim($ret['contacts']);


        //получаем ИД натуральной категории
        $ret['category_id'] = $this->getRealCategoryId($advert['farpost_category_id']);

        //сопоставляем параметры с реальными
        $ret['params'] = $this->classifyParams($advert['params'], $advert['farpost_category_id']);
        
        return $ret;
    }

    /**
     * Приводит в порядок объявление farpost. Результат этой функции пригоден для распознавания и классификации
     * @param type $advert
     */
    function normalize($advert) {
        $ret = array();

        $data = unserialize($advert['data']);

        //город
        $ret['city'] = trim($data['city']);

        //текст
        $ret['text'] = trim(html_entity_decode($data['text']));

        //цена
        $ret['price'] = $data['price'];

        //заголовок
        $ret['title'] = trim($data['title']);

        //картинки
        $ret['images'] = $data['images'];
        $ret['images_big'] = $data['images_big'];

        //время создания
        $ret['time'] = $advert['time'];
        if (!isset($data['params']['Добавлено']))
            $data['params']['Добавлено'] = $data['params']['Актуально'];
        $ret['create_time'] = strip_tags(html_entity_decode($data['params']['Добавлено']));
        $ret['expirate_time'] = strip_tags(html_entity_decode($data['params']['Актуально']));

        //ссылка на исходник
        $ret['source_link'] = $data['link'];

        //ИД объявления
        $ret['source_id'] = (int) strip_tags($data['params']['Объявление']);
        $ret['source_provider'] = 'farpost';

        //фильтруем параметры по умолчанию, оставляем только по существу
        $ret['params'] = array();
        foreach ($data['params'] as $id => &$val) {
            $id_l = mb_strtolower($id);
            if (!in_array($id_l, array('актуально', 'добавлено', 'объявление', 'цена', 'услуги'))) {
                $ret['params'][$id] = trim(strip_tags(html_entity_decode($val)));
            }
        }

        //контактные данные
        $ret['contacts'] = html_entity_decode($data['contacts']);
        $ret['farpost_parsed_id'] = $advert['farpost_parsed_id'];
        $ret['farpost_category_id'] = $data['farpost_category_id'];

        return $ret;
    }

    function addEmptyFilter($name, $farpostCategoryId) {
        $q = Yii::app()->db->createCommand()
                ->select()
                ->from('farpost_filter_relation')
                ->where("farpost_category_id=? AND name LIKE ?", array($farpostCategoryId, $name))
                ->query();
        if ($q->rowCount == 0) { //если ничего не найдено, то доабвляем
            Yii::app()->db->createCommand()->insert("farpost_filter_relation", array(
                "farpost_category_id" => $farpostCategoryId,
                "name" => $name
            ));
            return true;
        }
        return false;
    }

    function setFilterRelation($farpostFilterRelationId, $filterId) {
        return $this->createCommand()
                        ->update("farpost_filter_relation", array("filter_id" => (int) $filterId), "farpost_filter_relation_id=:frid", array(":frid" => $farpostFilterRelationId));
    }

    function clearParams($farpostFilterRelationId) {
        return $this->createCommand()
                        ->delete("farpost_param_relation", "farpost_filter_relation_id=:frid", array(":frid" => $farpostFilterRelationId));
    }

    /**
     * Функция пытается сопоставить параметры farpost параметрам из оригинальной базы данных
     * @param array $params исходные параметры в формате id=>value где id - имя параметра, value - имя значения параметра
     * @param int $farpostCategoryId Ид фарпост-категории в базе данных
     * @return array сопоставленные параметры формата id=>val
     */
    function classifyParams($params, $farpostCategoryId) {
        $ret = array();
        foreach ($params as $id => $val) {
            $newFilter = $this->filtrate($id, $val, $farpostCategoryId);
            if (is_array($newFilter)) {
                $ret[$newFilter['id']] = $newFilter['val'];
            }
        }
        return $ret;
    }
    
    /**
     * Учит парсер распознавать определенный параметр
     * @param int $farpostParsedId ид спарсенного объявления
     * @param int $filterId ид фильтра в базе сайта
     * @param int $paramId ид параметра в базе сайта
     */
    function learnFilter($farpostParsedId, $filterId, $paramId){
        $filterId = (int)$filterId;
        $paramId = (int)$paramId;
        if (!$filterId || !$paramId)
            return false;
        $advert = $this->getParseAdvertById($farpostParsedId);
        $advert = $this->normalize($advert);
        
        //получаем ИДы фильтров фарпоста, которые связаны с указанным фильтром сайта в категории, которой принадлежит объявление
        $farpostFilters = Yii::app()->db->createCommand()
                ->select("*")
                ->from("farpost_filter_relation")
                ->where(array("AND","filter_id=:fid","farpost_category_id=:fcid"),array(":fid"=>$filterId, ":fcid"=>$advert['farpost_category_id']))
                ->queryAll();
        
        
        //для каждого полученного фильтра проверяем, есть ли уже значение. Есть есть, обновляем. Иначе, добавляем новое
        foreach ($farpostFilters as $farpostFilter){
            $farpostFilterId = $farpostFilter["farpost_filter_relation_id"];
            $farpostFilterName = $farpostFilter["name"];
            if (isset($advert['params'][$farpostFilterName])){ //если фильтр указан для объявления
                $farpostParamName = $advert['params'][$farpostFilterName];
                //проверяем наличие значения этого фильтра в базе данных (ранее был обучен)
                $farpostParamId = Yii::app()->db->createCommand()
                        ->select("farpost_param_relation_id")
                        ->from("farpost_param_relation")
                        ->where(array("AND", "name LIKE :name", "farpost_filter_relation_id=:frid"),array(
                            ":name" => $farpostParamName,
                            ":frid" => $farpostFilterId
                        ))
                        ->queryScalar();
                if ($farpostParamId){
                    Yii::app()->db->createCommand()
                            ->update("farpost_param_relation", array("filter_param_id"=>$paramId), "farpost_param_relation_id=:fprid", array(":fprid"=>$farpostParamId));
                } else {
                    Yii::app()->db->createCommand()
                            ->insert("farpost_param_relation", array(
                                "farpost_filter_relation_id" => $farpostFilterId,
                                "name" => $farpostParamName,
                                "filter_param_id" => $paramId
                            ));
                }
            }
        }
        
        //gjkexftv BL
        Yii::app()->db->createCommand()
                ->select("farpost_param_relation.*")
                ->from("farpost_param_relation")
                ->join("farpost_filter_relation","farpost_filter_relation.farpost_filter_relation_id=farpost_param_relation.farpost_filter_relation_id")
                ->join("farpost_parsed","farpost_parsed.farpost_category_id=farpost_filter_relation.farpost_category_id")
                ->where(array("AND","farpost_parsed_id=:fpid","filter_id=:fid"));
    }

    //Далее обработчики фильтров FarPost. Все должны возвращать массив формата array(id=>'id',val=>'val'), либо false если параметр не найден
    //год в категории "продажа легковых автомобилей"
    function filterCarYear($year) {
        //получаем год выпуска из БД
        $id = Yii::app()->db->createCommand()
                ->select("filter_id")
                ->from('filter')
                ->where("name='Год' AND type='i'")
                ->queryScalar();

        if (!$id)
            return false;
        return array('id' => $id, 'val' => (int) $year);
    }

    //трансмиссия в продаже авто
    function filterTransmission($type) {
        
    }

    //пробег по россии в продаже авто
    function filterProbegPoRf($val) {
        
    }

}
