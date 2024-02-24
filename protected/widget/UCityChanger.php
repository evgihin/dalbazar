<?php

class UCityChanger extends CWidget {

    public function init() {
        $city = Yii::app()->user->getState("city",array('city_id'=>1,'name'=>'Владивосток','size'=>1)); //тут хранится Name, id и alias города по умолчанию

        $cCity = new City();
        $cities = $cCity->getAll(); //получаем список городов для вывода на экран
        $this->render('cities', array(
            'city' => $city,
            'cities' => $cities
        ));
    }

}

;
