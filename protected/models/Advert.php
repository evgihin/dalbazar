<?php

/**
 * Управление объявлениями
 */
class Advert extends CFormModel {

    public $limit = Null; //предел выборки ОТ
    public $offset = NULL; //предел выборки ДО
    public $onlyActive = true; //только активные объявления
    public $ordering = array(); //по какому принципу упорядочивать

    /** @var array Список фильтров S, которые необходимо применить к запросу. Пример задания: array(12,24,53,44) - применит параметры с ИД-ами 12,24,53,44 */
    private $_filterS = array();

    /** @var array Список фильтров, которые необходимо применить к запросу. Пример задания: array(12=>array('from'=>100,'to'=>200),24=>array('from'=>1,'to'=>3))
     *  - применит фильтры с ИД-ами 12 и значениями от 100 до 200 и ИД 24 от 1 до 3 */
    private $_filterI = array();
    private $withDeleted = false;
    private $stateId = NULL;


    /* далее атрибуты для валидации */
    public $zagolovok = ""; //обязательный, макс. 130 символов
    public $price = -1; //целое число [0,99999999]
    public $text = ""; //maxlength:3000
    public $category = 0; //целое число больше 0 + скрипт проверки существования категории в БД
    public $email = ""; //проверка на email
    public $city; //целое число больше нуля, проверка существования в базе
    public $deleteImage = array(); //картинки помеченные на удаление из объявления
    public $filterS = array(); //s-фильтры, пришедшие к нам из скрипта добавления объявления, формат array(id=>val) id - целое положительное число val - целое число от -1
    public $filterI = array(); //i-фильтры, пришедшие к нам из скрипта добавления объявления, формат array(id=>val) id - целое положительное число val - целое число от -1
    public $provider = "site";
    public $contacts = Null;
    public $link = Null;
    public $create_time = 0, $update_time = 0, $expirate_time = 0;
    public $user_id = 0;

    public function rules() {

        $regInteger = '/^\s*[+-]?\d+\s*$/'; //проверка целое ли число
        return array(
            array("zagolovok", "required", 'on' => 'add, edit, addParser', 'message' => 'Заголовок не указан'),
            array("zagolovok", "length", "max" => 130, 'on' => 'add, edit, addParser', 'message' => 'Заголовок слишком длинный'),
            array("price", "numerical", "integerOnly" => true, "min" => -1, "max" => 99999999, 'on' => 'add, edit, addParser', 'message' => 'Цена указана неверно'),
            array("text", "length", "max" => 3000, 'on' => 'add, edit, addParser', 'message' => 'Текст объявления слишком длинный'),
            array("category", "numerical", "integerOnly" => true, "min" => 1, "on" => "add, edit, addParser", "tooSmall" => "категория не указана", "message" => "категория указана неверно"),
            array("city", "numerical", "integerOnly" => true, "min" => 1, "on" => "add, edit, addParser", "tooSmall" => "город не указан", "message" => "город указан неверно"),
            array("category", "application.validators.vCategory", 'on' => 'add, edit, addParser'),
            array("city", "_cityCheck", 'on' => 'add, edit, addParser', 'message' => 'указанного города не существует'),
            array("email", "email", 'on' => 'add, edit', 'message' => 'E-mail указан неверно'),
            array("filterS, filterI", "_filterCheck", "pattern" => $regInteger, "on" => 'add, edit', 'message' => "фильтры указаны неверно"),
            array("deleteImage", "application.validators.vImage", 'owner' => Yii::app()->user->id, 'on' => 'edit', 'message' => 'Картинки помеченные на удаление указаны неверно'),
            array("provider, contacts, link, create_time, expirate_time", "required", 'on' => "addParser"),
            array("provider, link, create_time, expirate_time", "unsafe", 'on' => "addParser"),
            array("link", "url", 'on' => "addParser"),
            array("provider", "match", "pattern" => "~^[a-z][a-z0-9]{2,15}$~ui", 'on' => "addParser"),
        );
    }

    public function _cityCheck($attribute, $params) {
        $city = new City();
        if (!$city->checkAvailable((int) $this->$attribute)) {
            $this->addError($attribute, $params['message']);
        }
    }

    /**
     * проверяет фильтры I и S на верность (ид и значение - целое число) и проверяет существование их в БД
     * @param type $attribute
     * @param type $params
     */
    public function _filterCheck($attribute, $params) {
        $collect = array();
        foreach ($this->$attribute as $id => &$val) {
            if (!preg_match($params['pattern'], $val)) {
                $this->addError($attribute, $params['message']);
                return;
            }
            $id = (int) $id;
            $val = (int) $val;
            if ($id < 0 || $val < -1) {
                $this->addError($attribute, $params['message']);
                return;
            }
            if ($val != -1)
                $collect[$id] = $val;
        }
        $this->$attribute = $collect;

        $filter = new Filter();
        if ($attribute == "filterI") {
            if (count($this->$attribute) && !$filter->checkValuesI($this->$attribute)) {
                $this->addError($attribute, $params['message']);
                return;
            }
        }
        if ($attribute == "filterS") {
            if (count($this->$attribute) && !$filter->checkValuesS($this->$attribute)) {
                $this->addError($attribute, $params['message']);
                return;
            }
        }
    }

    /**
     * Создаем пустую команду для получения объявлений, которую потом можно модифицировать
     * @param bool $calcCount Если true, база данных будет по пути считать количество строк, попадающих под запрос (не считая LIMIT)
     * @return UCDbCommand Готовая чистая команда без фильтров
     */
    private function _createCommand() {
        $comm = Yii::app()->db->createCommand();
        $comm->select("advert.*");

        $comm->from('advert');
        if ($this->onlyActive) {
            $comm->where_and("advert.active=1");
        }

        $this->_attachLimit($comm);

        //ставим упорядочивание по умолчанию
        if (empty($this->ordering))
            $this->ordering['update_time'] = "DESC";
        $this->_order($comm);

        return $comm;
    }

    /**
     * Модифицирует запрос на попутный расчет количества запросов. После этой функции нельзя больше модифицировать SELECT
     * @param UCDbCommand $comm
     */
    private function _calculateCount(&$comm) {
        $select = str_replace('`', '', $comm->select); //двига автоматически ставит кавычки, убираем их чтобы не ставила много раз
        $comm->select($select, "SQL_CALC_FOUND_ROWS");
    }

    /**
     * Устанавливаем сортировку результатов запроса
     * @param string $column имя поля, по которому упорядочивать
     * @param string $pos направление ASC либо DESC
     */
    public function order($column, $pos = "ASC") {
        $this->ordering[$column] = $pos;
    }

    /**
     * @return UCDbCommand объект запроса с заполненными полями
     */
    private function _createFullCommand() {
        $comm = $this->_createCommand();
        $this->_attachCategory($comm);
        $this->_attachState($comm);
        $this->_attachImage($comm);
        return $comm;
    }

    /**
     * добавляет главную картинку в результат запроса
     * @param UCDbCommand $comm
     */
    private function _attachImage(&$comm) {
        $select = str_replace('`', '', $comm->select); //двига автоматически ставит кавычки, убираем их чтобы е ставила много раз
        $comm->select($select . ', image.name as image');
        $comm->leftJoin('image', "advert.advert_id=image.advert_id AND image.main<>0");
    }

    /**
     * добавляет категорию в результат запроса
     * @param UCDbCommand $comm
     */
    private function _attachCategory(&$comm) {
        $select = str_replace('`', '', $comm->select); //двига автоматически ставит кавычки, убираем их чтобы е ставила много раз
        $comm->select($select . ', category.name AS category, category.category_parent_id');
        $comm->leftJoin('category', "advert.category_id=category.category_id");
    }

    /**
     * добавляет информацию о состоянии в результаты запроса
     * @param UCDbCommand $comm
     */
    private function _attachState(&$comm) {
        $select = str_replace('`', '', $comm->select); //двига автоматически ставит кавычки, убираем их чтобы е ставила много раз
        $comm->select($select . ', state.*');
        $comm->leftJoin('(SELECT 
                        advert_state_history_id,
                        advert_id as advert_id2,
                        advert_state.advert_state_id,
                        advert_state.`name` as state_name,
                        advert_state.description as state_info,
                        advert_state.alias as state,
                        advert_state_history.description as state_description
                FROM advert_state_history INNER JOIN advert_state ON (advert_state_history.advert_state_id=advert_state.advert_state_id)
                ORDER BY time DESC) AS state', "advert.advert_id = state.advert_id2");
        $comm->group("advert.advert_id");
    }

    /**
     * добавляет упорядочивание результатов
     * @param UCDbCommand $comm
     */
    private function _order(&$comm) {
        $ord = "";
        if (!empty($this->ordering)) {
            foreach ($this->ordering as $key => $val)
                $ord.=$key . " " . $val . ", ";
            $ord = mb_substr($ord, 0, -2);
            $comm->order($ord);
        }
    }

    /**
     * Добавляет к запросу лимит выдачи 
     * @param UCDbCommand $comm
     */
    private function _attachLimit(&$comm) {
        if (!is_null($this->limit)) {
            $comm->limit($this->limit, $this->offset);
        }
    }

    /**
     * Добавляет к запросу фильтр по ИД-у объявления
     * @param UCDbCommand $comm
     */
    private function _attachAdvertId(&$comm, $advert_id) {
        $comm->where_and("advert_id=:aid", array(":aid" => $advert_id));
    }

    /**
     * Вставляет объявление в базу данных
     */
    public function insert() {
        if (!$this->create_time)
            $this->create_time = time();
        if (!$this->expirate_time)
            $this->expirate_time = time() + Yii::app()->params['advertLifeTime'];

        $active = !Yii::app()->params['needModerateAfterPublish'];

        $command = Yii::app()->db->createCommand();
        //вставляем объявление и получаем ИД
        $command->insert('advert', array(
            'category_id' => $this->category,
            'user_id' => $this->user_id,
            'zagolovok' => $this->zagolovok,
            'text' => $this->text,
            'city_id' => $this->city,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
            'expirate_time' => $this->expirate_time,
            'active' => $active,
            'price' => ($this->price === "" || $this->price === -1) ? -1 : $this->price,
            'email' => $this->email,
            'provider' => $this->provider,
            'contacts' => $this->contacts,
            'link' => $this->link
        ));
        $id = Yii::app()->db->lastInsertID;

        //обновляем статус объявления
        State::update($id, 'created', 'Добавлено пользователем через страницу добавления');
        if (Yii::app()->params['needModerateAfterPublish'])
            State::update($id, 'waited', 'Отправлено на модерацию сразу после публикации');
        else
            State::update($id, 'published', 'Опубликовано сразу после добавления (отмодерировано автоматически)');

        //пишем фильтра
        if (!$this->scenario != "addParser") {
            $this->_insertFilterI($id);
            $this->_insertFilterS($id);
        }

        return $id;
    }

    private function _insertFilterS($advertId) {
        $command = Yii::app()->db->createCommand();
        foreach ($this->filterS as $id => $val) {
            $command->insert('advert_filter', array(
                'advert_id' => $advertId,
                'filter_id' => $id,
                'filter_param_id' => $val
            ));
        }
    }

    private function _insertFilterI($advertId) {
        $command = Yii::app()->db->createCommand();
        foreach ($this->filterI as $id => $val) {
            $command->insert('advert_filter', array(
                'advert_id' => $advertId,
                'filter_id' => $id,
                'value' => $val
            ));
        }
    }

    public function update($advertId) {
        $command = Yii::app()->db->createCommand();
        //вставляем объявление и получаем ИД
        $command->update('advert', array(
            'category_id' => $this->category,
            'user_id' => (Yii::app()->user->isGuest) ? 0 : Yii::app()->user->id,
            'zagolovok' => $this->zagolovok,
            'text' => $this->text,
            'city_id' => $this->city,
            'expiration' => time() + (86400 * 30),
            'price' => ($this->price === "" || $this->price === -1) ? -1 : $this->price,
            'phone1' => $this->phone1,
            'phone2' => $this->phone2,
            'email' => $this->email
                ), 'advert_id=:aid', array(':aid' => $advertId));
        $id = $advertId;

        //обновляем статус объявления
        State::update($id, 'updated', 'Обновлено пользователем через личный кабинет');
        State::update($id, 'published', 'Опубликовано сразу после обновления');

        //чистим все фильтры у объявления
        $filter = new Filter();
        $filter->clearByAdvert($advertId);

        //пишем фильтры
        $this->_insertFilterI($id);
        $this->_insertFilterS($id);

        //вставляем картинки
        $image = new Image();
        $image->moveTempToAdvert($this->images, $id, $this->mainPicture);

        //удаляем картинки помеченные на удаление
        $image->remove($this->deleteImage);

        return $id;
    }

    /**
     * Применяет строковые и int-фильтры к запросу в БД. <B>ВНИМАНИЕ! ПОСЛЕ ПРИМЕНЕНИЯ НЕЛЬЗЯ ЗАДАВАТЬ ПАРАМЕТР WHERE ЗАПРОСУ!</B>
     * @param UCDbCommand $command комманда для изменения
     * @return UCDbCommand Измененная комманда
     */
    private function _appendFilters(&$command) {

        if ($this->_filterS || $this->_filterI) {
            $i = 0;

            $command2 = YII::app()->db->createCommand();

            //получить количество фильтров из БД чтобы потом проверить сколько фильтров удовлетворило условию
            $filterCount = 0;
            if ($this->_filterS) {
                $command2->select('filter.filter_id')
                        ->from('filter')
                        ->group('filter.filter_id');
                $command2->where(array('IN', 'filter_param.filter_param_id', $this->_filterS));
                $command2->join('filter_param', 'filter_param.filter_id=filter.filter_id');
                $res = $command2->query();
                $filterCount += (int) $res->rowCount;
                $command2->reset();
            }
            if ($this->_filterI)
                $filterCount+=count($this->_filterI);



            //собираем фильтры
            //собираем фильтр I
            $i = 0;
            if ($this->_filterI) {
                foreach ($this->_filterI as $id => $val) {
                    $command2->where_or(array('AND', 'advert_filter.filter_id = :fid' . $i, 'advert_filter.value >= :ffrom' . $i, 'advert_filter.value <= :fto' . $i), array(
                        ':fid' . $i => (int) $id,
                        ':ffrom' . $i => (int) $val['from'],
                        ':fto' . $i => $val['to'],
                    ));
                    $i++;
                }
            }

            //собираем фильтр S
            if ($this->_filterS) {
                $command2->where_or(array('IN', 'advert_filter.filter_param_id', $this->_filterS));
            }

            //получаем список объявлений и применяем его к запросу
            $command2->select('advert_id')
                    ->from('advert_filter')
                    ->group('advert_id')
                    ->having('COUNT(advert_id) = :acount', array(':acount' => $filterCount));

            $advert_ids = $command2->queryColumn();
            if ($advert_ids) {
                $newWhere = array('IN', 'advert_id', $advert_ids);
            } else {
                $newWhere = '0=1';
            }

            //обновляем command WHERE
            $command->where_and($newWhere);
        }

        //проверяем, запрашивать только удаленные или не только
        if (!$this->withDeleted && $this->stateId != 4) {
            $command->leftJoin("(SELECT 
                        advert_state_history_id,
                        advert_id as advert_id2,
                        b.advert_state_id,
                        b.`name` as state_name,
                        b.description as state_info,
                        b.alias as state,
                        a.description as state_description
                FROM advert_state_history AS a INNER JOIN advert_state AS b ON (a.advert_state_id=b.advert_state_id) ORDER BY a.time DESC) AS states", "states.advert_id2=advert.advert_id");
            $command->where_and('states.advert_state_id!=4');
            $command->group("advert_id");
        }

        //смотрим, мож какое состояние запросить
        if ($this->stateId !== NULL) {
            $command->leftJoin("(SELECT 
                        advert_state_history_id,
                        advert_id as advert_id2,
                        b.advert_state_id,
                        b.`name` as state_name,
                        b.description as state_info,
                        b.alias as state,
                        a.description as state_description
                FROM advert_state_history AS a INNER JOIN advert_state AS b ON (a.advert_state_id=b.advert_state_id) ORDER BY a.time DESC) AS states", "states.advert_id2=advert.advert_id");
            $command->where_and('states.advert_state_id=:stateid', array(':stateid' => $this->stateId));
            $command->group("advert_id");
        }

        return $command;
    }

    public function get() {
        $comm = Yii::app()->db->createCommand()->select()->from('advert');
        $this->_appendFilters($comm);
        return $comm->queryAll();
    }

    public function setFilterI(array $arr) {
        foreach ($arr as $id => $value) {
            if (!preg_match("/^\d+$/", $id))
                throw new CException('В setFilterS указан массив с нецелочисленным ключем');
            if (!isset($value['from']) || !preg_match("/^\d+$/", $value['from']))
                throw new CException('В setFilterS[' . $id . '][from] хранится значение, отличное от числа, либо оно вообще не задано');
            if (!isset($value['from']) || !preg_match("/^\d+$/", $value['to']))
                throw new CException('В setFilterS[' . $id . '][to] хранится значение, отличное от числа, либо оно вообще не задано');
        }
        $this->_filterI = array_merge($this->_filterI, $arr);
    }

    public function setFilterS(array $arr) {
        foreach ($arr as $val) {
            if (!preg_match("/^\d+$/", $val)) {
                throw new CException('В элементе массива, переданного в функцию setFilterI хранится неверное значение');
            }
        }
        $this->_filterS = array_merge($this->_filterS, $arr);
    }

    public function getFilterI() {
        return $this->_filterI;
    }

    public function getFilterS() {
        return $this->_filterS;
    }

    /**
     * Задать фильтр I напрямую из формы
     * @param array $arr Массив, переданный в POST
     * @throws CHttpException посылает в случае передачи неверных данных
     */
    public function setFilterIFromQuery(array $arr) {
        $newI = array();
        if ($arr)
            foreach ($arr as $id => $value) {
                if (
                        !Helpers::checkId($id) ||
                        !Helpers::required($value, array('from', 'to', 'min', 'max', false)) ||
                        !preg_match("/^\d+$/", $value['from']) ||
                        !preg_match("/^\d+$/", $value['min']) ||
                        !preg_match("/^\d+$/", $value['to']) ||
                        !preg_match("/^\d+$/", $value['max'])
                )
                    throw new CHttpException(400, 'Неверные параметры переданы на сервер');
                if ($value['from'] != $value['min'] || $value['to'] != $value['max']) {
                    $newI[$id] = $value;
                }
            }
        $this->_filterI = array_merge($this->_filterI, $newI);
    }

    /**
     * Устанавливает фильтры S, полученные из запроса.
     * @param array $arr Массив фильтров, заданный по принципу: {2:'on',3:'on'5:'on',6:'on'}
     * @throws CHttpException
     */
    public function setFilterSFromQuery(array $arr) {
        if ($arr) {
            $this->_filterS = array_merge($this->_filterS, array_keys($arr, 'on'));
        }
    }

    /**
     * Получает список объявлений из категории, применяя лимиты и фильтры
     * @param int $categoryId ИД категории, из которой получать объявления
     * @return array список объявлений, отсортированных и сгруппированных по ИД-у категории
     */
    public function getByCategory($categoryId) {
        //настраиваем основной запрос, в котором получим список объявлений
        $command = $this->_createCommand()
                ->where_and(array('OR', 'advert.category_id=:cid', 'advert.category_id IN (SELECT category_id FROM category WHERE category_parent_id=:cid)'), array(
            ':cid' => $categoryId,
        ));

        //цепляем фильтры и лимиты
        $this->_appendFilters($command);

        return $command->queryAll();
    }

    public function getByCategoryFull($categoryId, $calcCount = false) {
        $command = $this->_createFullCommand(true)
                ->where_and(array('OR', 'advert.category_id=:cid', 'advert.category_id IN (SELECT category_id FROM category WHERE category_parent_id=:cid)'), array(
            ':cid' => $categoryId,
        ));

        //цепляем фильтры и лимиты
        $this->_appendFilters($command);

        if ($calcCount)
            $this->_calculateCount($command);

        return $command->queryAll();
    }

    public function getIdAttachedByCategory($categoryId) {
        return Yii::app()->db->createCommand()
                        ->select('advert_id')
                        ->from('advert_attached')
                        ->where(array('AND', 'category_id=:cid', 'creation < :date', ':date < expiration'), array(':cid' => $categoryId, ':date' => time()))
                        ->queryColumn();
    }

    /**
     * Получить список объявлений, опубликованных определенным пользователем
     * @param int $userId ИД пользователя, для которого получить объявления
     * @return array масссив объявлений
     */
    public function getByUser($userId) {
        $comm = $this->_createCommand()->where_and('user_id=:uid', array(':uid' => $userId));
        return $comm->queryAll();
    }

    public function getByUserFull($userId) {
        $comm = $this->_createFullCommand()->where_and('user_id=:uid', array(':uid' => $userId));
        return $comm->queryAll();
    }

    public function getByAdvert($advertId) {
        if (!is_array($advertId))
            $adverts = array($advertId);
        else
            $adverts = $advertId;
        $result = $this->_createCommand()
                ->where(array('IN', 'advert_id', $adverts));
        if (is_array($advertId))
            return $result->queryAll();
        else
            return $result->queryRow();
    }

    public function getByAdvertFull($advertId) {
        if (!is_array($advertId))
            $adverts = array($advertId);
        else
            $adverts = $advertId;

        $comm = $this->_createFullCommand();
        $comm->where_and(array("in", 'advert.advert_id', $adverts));

        if (is_array($advertId))
            return $comm->queryAll();
        else
            return $comm->queryRow();
    }

    /**
     * Получает количество новых объявлений с момента последней проверки
     * @return int
     */
    public function getNewCount() {
        return Yii::app()->db
                        ->createCommand('SELECT COUNT(*) FROM advert WHERE creation>:creation')
                        ->queryScalar(array(':creation' => Yii::app()->user->getState('lastAdvertCheck', 0)));
    }

    /**
     * Получаем лидеров просмотра объявлений
     */
    public static function getTopViews($count) {
        return Yii::app()->db
                        ->createCommand()
                        ->select()
                        ->from('advert_full')
                        ->where("state_alias='published'")
                        ->order("views DESC")
                        ->limit($count)
                        ->queryAll();
    }

    public static function getTopViewsWithImage($count) {
        return Yii::app()->db->createCommand("
            SELECT a.*, d.`name` as image FROM advert AS a 
            LEFT JOIN image AS d ON (a.advert_id=d.advert_id AND d.main<>0)
            WHERE active=1
            GROUP BY a.advert_id
            ORDER BY views DESC
            LIMIT 0,:count")->bindParam(":count", $count)->queryAll();
    }

    public static function getLastest($count) {
        return Yii::app()->db
                        ->createCommand()
                        ->select()
                        ->from('advert')
                        ->where("active=1")
                        ->order("create_time DESC")
                        ->limit($count)
                        ->queryAll();
    }

    //получить последние добавленные объявления с картинками
    public static function getLastestWithImage($count) {
        return Yii::app()->db->createCommand("
            SELECT a.*, d.`name` as image FROM advert AS a 
            LEFT JOIN image AS d ON (a.advert_id=d.advert_id AND d.main<>0)
            WHERE active=1
            GROUP BY a.advert_id
            ORDER BY create_time DESC
            LIMIT 0,:count")->bindParam(":count", $count)->queryAll();
    }

    /**
     * Регистрируем просмотр объявления пользователем
     * @param $advertId ИД объявления
     */
    public static function registerView($advertId) {
        Yii::app()->db
                ->createCommand('UPDATE advert SET views=views+1 WHERE advert_id=:aid')
                ->execute(array(":aid" => $advertId));
    }

    /**
     * Прикрепляет объявление к верху категории
     * @param int $advertId ИД объявления, которое нужно прикрепить
     */
    public function attach($advertId) {
        $advert = $this->getByAdvert($advertId);
        Yii::app()->db->createCommand()->insert('advert_attached', array(
            'advert_id' => $advertId,
            'category_id' => $advert['category_id'],
            'creation' => time(),
            'expiration' => time() + ( Yii::app()->params['topAdvertTime'] * 60 * 60 )
        ));
        return Yii::app()->db->lastInsertID;
    }

    //проверяет наличие объявления в базе. Если $onlyPublished=true, то проверяет и статус объявления
    //Вернет true если объявление присутствует в базе и опубликовано
    public function checkAvailability($advertId) {
        $comm = $this->_createCommand()->where_and("advert.advert_id=:aid", array(":aid" => $advertId));
        $res = $comm->queryRow();
        return !empty($res);
    }

    public function count() {
        return Yii::app()->db->createCommand("SELECT FOUND_ROWS();")->queryScalar();
    }

    public function delete($advertId, $comment) {
        return State::set($advertId, "deleted", $comment);
    }

    /**
     * Получить объявления, соответствующие определенному состоянию
     * @param type $alias имя состояния (его алиас в БД)
     * @param type $limit лимит
     * @param type $offset сдвиг, для постраничного отображения
     * @return array массив объявлений в определенном состоянии
     */
    public function getByState($alias, $userId = null) {
        $comm = $this->_createCommand();
        $this->_attachState($comm);
        $comm->where_and('state=:alias', array(":alias" => $alias));
        if (!is_null($userId))
            $comm->where_and('advert.user_id=:uid', array(":uid" => $userId));
        return $comm->queryAll();
    }

    public function getByStateFull($alias, $userId = null) {
        $comm = $this->_createFullCommand();
        $comm->where_and('state=:alias', array(":alias" => $alias));
        if (!is_null($userId))
            $comm->where_and('advert.user_id=:uid', array(":uid" => $userId));
        return $comm->queryAll();
    }

    //Поднять объявление (будто оно только что добавлено)
    public function pickUp($advertId) {
        $time = time();
        Yii::app()->db
                ->createCommand()
                ->update('advert', array('update_time' => $time), "advert_id=:aid", array(":aid" => $advertId));
        Feature::add($advertId, 'pick_up');
    }

    public function setBold($advertId) {
        Feature::add($advertId, 'bold');
    }

}
