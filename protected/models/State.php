<?php

class State extends CFormModel {

    /**
     * Обновляет состояние объявления
     * @param int $advertId ИД обновляемого объявления
     * @param mixed $stateId ИД состояния, либо его алиас
     * @param string $description описание состояния
     */
    static function update($advertId, $stateId, $description) {
        if (is_string($stateId) && !preg_match("/^[a-z]+$/i", $stateId))
            throw new CException('Неверно указано состояние объявления');
        Yii::app()->db->createCommand()
                ->insert('advert_state_history', array(
                    'advert_id' => $advertId,
                    'advert_state_id' => (is_integer($stateId)) ? $stateId : new CDbExpression('(SELECT advert_state_id FROM advert_state WHERE alias=:alias)', array(':alias' => $stateId)),
                    'time' => time(),
                    'description' => $description
        ));

        //если объявление опубликовано, обновить статус в карточке объявления
        if ($stateId == "published" || $stateId == 1)
            Yii::app()->db->createCommand()
                    ->update("advert", array("active" => 1), "advert_id=:aid", array(":aid" => $advertId));

        //если объявление снято с публикации, убираем статус "опубликовано"
        if ($stateId != "published" && $stateId != 1)
            Yii::app()->db->createCommand()
                    ->update("advert", array("active" => 0), "advert_id=:aid", array(":aid" => $advertId));
    }

    /**
     * Алиас update
     * @param int $advertId ИД обновляемого объявления
     * @param mixed $stateId ИД состояния, либо его алиас
     * @param string $description описание состояния
     */
    static function set($advertId, $stateId, $description) {
        return State::update($advertId, $stateId, $description);
    }

}

