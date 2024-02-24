<?php

class ParserController extends CAdminController {
    
  var $defaultAction = 'index';

  public function actionIndex(){
    //$this->act = array('add'=>'admin/user/add','save'=>'admin/user/save','apply'=>'admin/user/save');
    $this->render('index');
    //echo 'asd';
  }

  public function actionError() {
    if ($error = Yii::app()->errorHandler->error) {
      if (Yii::app()->request->isAjaxRequest)
        echo $error['message'];
      else
        $this->render('error', $error);
    }
  }

}