<?php

class vCity extends CArrayValidator {

    public function validateValue($cityId) {
        return Yii::app()->db->createCommand()
                ->select("count(*)")
                ->from("city")
                ->where('city_id=:cid', array(":cid" => (int) $cityId))
                ->queryScalar();
    }

}
