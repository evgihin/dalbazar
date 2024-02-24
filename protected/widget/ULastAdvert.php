<?php

class ULastAdvert extends CWidget {

  public function init() {
    $data = Yii::app()->db->createCommand()
            ->select('advert_id,zagolovok')
            ->from('advert')
            ->where('active=1')
            ->order('update_time DESC')
            ->limit(Yii::app()->params['countLastAdvert'])
            ->query();
    echo CHtml::openTag('ul');
    foreach ($data as $d) {
      echo CHtml::openTag('li');
      echo CHtml::link($d['zagolovok'], 'category/' . $d['advert_id']);
      echo CHtml::closeTag('li');
    }
    echo CHtml::closeTag('ul');
  }

}

;
