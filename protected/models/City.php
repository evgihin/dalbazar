<?php

class City extends CFormModel {

    public function checkAvailable($cityId) {
        return Yii::app()->db->createCommand()
                        ->select()
                        ->from('city')
                        ->where('city_id=:cid', array(":cid" => $cityId))
                        ->queryScalar();
    }

    public function getAll() {
        return Yii::app()->db->createCommand()
                        ->select()
                        ->from('city')
                        ->order('name ASC')
                        ->queryAll();
    }

    public function getByName($name) {
        return Yii::app()->db->createCommand()
                        ->select()
                        ->from('city')
                ->where("MATCH `name` AGAINST(:cit)", array(":cit"=>$name))
                        ->queryRow();
    }

}

