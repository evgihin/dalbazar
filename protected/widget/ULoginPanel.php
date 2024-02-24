<?php

class ULoginPanel extends CWidget {

  public function init() {
    //Проверяем, залогинен ли юзер или нет
    if (Yii::app()->user->isGuest){
      Yii::app()->user->setReturnUrl(Yii::app()->getRequest()->getUrl());
      $this->render('login');
    } else {
      $advert = new Advert();
      $advert->onlyActive = false;
      $advert->getByState('waited',Yii::app()->user->id);
      $waitCount = $advert->count();
      $advert->getByState('published', Yii::app()->user->id);
      $publishedCount = $advert->count();
      $this->render('logined',array(
          'waitCount'=>$waitCount,
          'publishedCount'=>$publishedCount
      ));
    }
  }

}

;
