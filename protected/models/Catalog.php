<?php

class Catalog extends CExtFormModel {

    var $order = "catalog.pos ASC";
    //протестировать интерфейс каталога
    var $catalog_parent_id;
    var $name;
    var $pos = 0;
    var $create_time = 0;

    function rules() {
        return array(
            array("catalog_parent_id, pos, create_time", "numerical", "integerOnly" => TRUE),
            array("catalog_parent_id, name", "required"),
            array("name", "length", "min" => 3, "max" => 150),
            array("create_time", "unsafe"),
            array("catalog_parent_id", "validateExists")
        );
    }

    public function validateExists($attribute, $params) {
        if ($this->$attribute!=0 && !$this->exists($this->$attribute))
            $this->addError($attribute, "Такой родительской категории не существует");
    }

    public function get($table = "catalog", $where = "removed=0", $params = array(), $count = false) {
        return parent::get($table, $where, $params, $count);
    }
    
    public function getById($catalogId){
        return $this->createCommand()
                ->select()
                ->from("catalog")
                ->where("catalog_id=:cid", array(":cid"=>$catalogId))
                ->queryRow();
    }
    
    public function getByParentId($parentId) {
        return $this->createCommand()
                ->select()
                ->from("catalog")
                ->where("catalog_parent_id=:pid", array(":pid"=>$parentId))
                ->queryAll();
    }
    
    public function getRecursivePath($where = "removed=0", $params = array()){
        $items = $this->createCommand()->select("catalog_id, path")
                ->from("catalog")
                ->where($where, $params)
                ->order("level ASC, pos ASC")
                ->queryAll();
        $result = array();
        foreach ($items as $item) {
            $pathItem = &$result;
            $pathArr = explode(".", $item['path']);
            foreach($pathArr as $path){
                if (!isset($pathItem[$path])){
                    $pathItem[$path] = array();
                }
                $pathItem = &$pathItem[$path];
            }
        }
        return $result;
    }
    
    /**
     * Получить данные в формате одномерного массива, где список категорий
     * @param type $catalogId
     * @return type
     */
    public function getDropDownListArray($where = "removed=0", $params = array()){
        $items = Helpers::setId($this->get("catalog", $where, $params), "catalog_id");
        $path = $this->getRecursivePath($where, $params);
        return $this->_getDDListRecursion($path[0], $items);
    }
    
    private function _getDDListRecursion(&$path, &$items){
        $result = array();
        foreach ($path as $id => $subPath) {
            if (!isset($items[$id])) //если отсутствует категория, совпадающая пути, отклюняем ветку рекурсии
                continue;
            $result[$id] = str_repeat("-", $items[$id]['level']-1) . $items[$id]['name'];
            if ($subPath)
                $result += $this->_getDDListRecursion($subPath, $items);
        }
        return $result;
    }

    function exists($catalogId) {
        return Yii::app()->db->createCommand()->select("COUNT(*)")
                        ->from("catalog")
                        ->where("catalog_id=:cid", array(":cid" => $catalogId))
                        ->queryScalar();
    }

    function insert() {
        if (!$this->create_time)
            $this->create_time = time();
        
        //сдвигаем позиции всех элементов что после вставленного на единицу
        $this->movePosForward($this->pos, $this->catalog_parent_id);

        if (!$this->create_time)
            $this->create_time = time();
        Yii::app()->db->createCommand()->insert("catalog", $this->getAttributes(array("catalog_parent_id", "name", "pos", "create_time")));
        $id = Yii::app()->db->lastInsertID;
        $this->setPath($id); //прописываем путь до дерева каталогов
        return $id;
    }
    
    function update($catalogId){
        $old = $this->getById($catalogId);
        
        //раздвигаем позицию для вставки элемента
        $this->movePosForward($this->pos, $this->catalog_parent_id);
        //задвигаем старую ячейку
        $this->movePosBack($old['pos'], $old['catalog_parent_id']);
        
        Yii::app()->db->createCommand()->update("catalog", 
                $this->getAttributes(array("catalog_parent_id", "name", "pos")), 
                "catalog_id=:cid", array(":cid"=>$catalogId));
        $this->setPath($catalogId); //прописываем путь до дерева каталогов
        
    }

    /**
     * Удалить в корзину
     * @param int $catalogId
     */
    function remove($catalogId) {
        if ($this->exists($catalogId) && !$this->removed($catalogId)) {
            Yii::app()->db->createCommand()->update("catalog", array("removed" => 1), "catalog_id=:cid", array(":cid" => $catalogId));
            return true;
        } else
            return false;
    }

    /**
     * Восстановить из корзины
     * @param int $catalogId
     */
    function recover($catalogId) {
        if ($this->removed($catalogId)) {
            Yii::app()->db->createCommand()->update("catalog", array("removed" => 0), "catalog_id=:cid", array(":cid" => $catalogId));
            return true;
        } else
            return false;
    }

    /**
     * Проверяет, удален ли элемент
     * @param int $catalogId
     * @return bool true если элемент в корзине
     */
    function removed($catalogId) {
        return Yii::app()->db->createCommand()->select("COUNT(*)")
                        ->from("catalog")
                        ->where("catalog_id=:cid AND removed=1", array(":cid" => $catalogId))
                        ->queryScalar();
    }

    /**
     * Удаляет элемент и всех его детей. Удалить можно только из корзины 
     * @todo сделать удаление связей между категориями и каталогом и удаление элементов каталога
     * @param int $catalogId
     * @return boole true если удаление прошло успешно
     */
    function delete($catalogId) {
        if ($this->removed($catalogId)) {
            Yii::app()->db->createCommand()->delete("catalog", "catalog_id=:cid", array(":cid" => $catalogId));

            //ищем и рекурсивно удаляем детей
            $q = Yii::app()->db->createCommand()->select('catalog_id')->from('catalog')->where("catalog_parent_id=:cpi", array(":cpi" => $catalogId))->query();
            foreach ($q as $catalog) {
                $this->remove($catalog['catalog_id']);
                $this->delete($catalog['catalog_id']);
            }
            return true;
        } else
            return false;
    }

    /**
     * Прописывает путь для элемента дерева каталога
     * @param int $catalogId
     * @param bool $recursive обходить рекурсивно детей
     */
    public function setPath($catalogId, $recursive=true) {
        $origCatalogId = $catalogId;
        $path = array();

        //проходимся по дереву и собираем путь
        $comm = Yii::app()->db->createCommand()->select();
        do {
            $path[] = $catalogId;
            $parentId = $comm->select("catalog_parent_id")->from("catalog")->where("catalog_id=:cid", array(":cid" => $catalogId))->queryScalar();

            //защита от рекурсии
            if (in_array($parentId, $path))
                break;

            $catalogId = $parentId;
        } while ($parentId !== false);
        
        //поулчаем путь в виде текста и обновляем в БД
        $lvl = count($path) - 1;
        $path = implode(".", array_reverse($path));
        $comm->update("catalog", array("path" => $path, "level" => $lvl), "catalog_id=:cid", array(":cid" => $origCatalogId));
        
        if ($recursive){
            $children = $this->getByParentId($origCatalogId);
            if ($children)
                foreach ($children as $child){
                    $this->setPath($child['catalog_id']);
                }
        }
        
        return $path;
    }
    
    /** 
     * Сдвигает позиции всех "братьев и сестер" в дереве вперед
     * @param int $pos Позиция, начиная с которой сдвигать
     * @param int $parentId ИД родителя, детей которого обрабатывать
     */
    private function movePosForward($pos,$parentId){
        Yii::app()->db->createCommand("UPDATE catalog SET pos=pos+1 WHERE pos>=:p AND catalog_parent_id=:cpi")->query(array(
            ":p" => $pos,
            ":cpi" => $parentId
        ));
    }
    
    /**
     * Сдвигает позиции всех братьев и сестер назад (при удалении элемента из ветки)
     * @param int $pos Позиция, начиная с которой сдвигать
     * @param int $parentId ИД родителя, детей которого обрабатывать
     */
    private function movePosBack($pos,$parentId) {
        Yii::app()->db->createCommand("UPDATE catalog SET pos=pos-1 WHERE pos>=:p AND catalog_parent_id=:cpi")->query(array(
            ":p" => $pos,
            ":cpi" => $parentId
        ));
    }

}
