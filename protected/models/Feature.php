<?php

class Feature extends CFormModel {
    private $_adverts = array();
    private $_features = array();
    
    public function __construct($advertIds=array(), $features = array('bold', 'pick_up', 'pictured')) {
        $this->_adverts = $advertIds;
        
        //исходный запрос
        $req = Yii::app()->db->createCommand()
                ->select("advert_id")
                ->from("advert_feature_history as a")
                ->where(array('IN', 'advert_id', $this->_adverts))
                ->group("advert_id")
                ->where_and("(a.creation<=UNIX_TIMESTAMP() AND UNIX_TIMESTAMP()<=a.expiration ) OR 
			(a.creation<=UNIX_TIMESTAMP() AND a.expiration=0)")
                ->where_and("feature_name=:feature");

        foreach ($features as $featureName){
            $this->_features[$featureName] = $req->queryColumn(array(":feature"=>$featureName));
        }
    }
    
    public function bold($advertId){
        return $this->check($advertId, "bold");
    }
    
    public function pictured($advertId){
        return $this->check($advertId, "pictured");
    }
    
    public function pickUp($advertId){
        return $this->check($advertId, "pick_up");
    }
    
    public function check($advertId,$name){
        if (!in_array($advertId, $this->_adverts))
            throw new CException("Задан ИД, не указанный при инициализации класса");
        
        if (!isset($this->_features[$name]))
            throw new CException("Такой фичи не существует, либо она не была указана при инициализации класса");
        
        return in_array($advertId,$this->_features[$name]);
    }

    /**
     * Получает массив платных примочек для определенного объявления либо массива объявлений
     * @param mixed $advert_id либо ид объявления либо массив ИД-ов
     * @return array массив ФИЧ сайта
     */
    public function getByAdvert($advert_id) {
        if (!is_array($advert_id))
            $ids = array($advert_id);
        else
            $ids = $advert_id;
        $req = Yii::app()->db->createCommand()
                ->select("advert.advert_id, 
                                    count(a.advert_id) as bold, 
                                    count(b.advert_id) as pictured,
                                    count(c.advert_id) as pick_up   ")
                ->from("advert")
                ->group("advert.advert_id")
                ->leftJoin("advert_feature_history AS a", array("AND", "a.advert_id = advert.advert_id", "a.feature_name='bold'"))
                ->leftJoin("advert_feature_history AS b", array("AND", "b.advert_id = advert.advert_id", "b.feature_name='pictured'"))
                ->leftJoin("advert_feature_history AS c", array("AND", "c.advert_id = advert.advert_id", "c.feature_name='pick_up'"))
                ->where(array('IN', 'advert.advert_id', $ids))
                //выбираем только текущие фичи
                ->where_and("(a.creation<=UNIX_TIMESTAMP() AND UNIX_TIMESTAMP()<=a.expiration ) OR 
			(a.creation<=UNIX_TIMESTAMP() AND a.expiration=0)");

        if (!is_array($advert_id))
            return $req->queryRow();
        else
            return $req->queryAll();
    }

    /**
     * Добавляет фичу к объявлению
     * @param int $advertId ИД объявления, для которого добавить фичу
     * @param string $featureName имя фичи. Пока доступно 3: bold,pictured, pick_up
     * @throws CException
     */
    public static function add($advertId, $featureName) {
        switch ($featureName) {
            case "bold" :
                $arr['creation'] = time();
                $arr['expiration'] = 0;
                $arr['feature_name'] = 'bold';
                break;
            case "pictured":
                $arr['creation'] = time();
                $arr['expiration'] = time() + 86400 * 3; //три дня
                $arr['feature_name'] = 'pictured';
                break;
            case "pick_up":
                $arr['creation'] = time();
                $arr['expiration'] = 0;
                $arr['feature_name'] = 'upped';
                break;
        }
        if (!$arr)
            throw new CException("featureName принимает неизвестное значение");
        $arr['advert_id'] = $advertId;
        Yii::app()->db->createCommand()->insert('advert_feature_history', $arr);
    }

}

