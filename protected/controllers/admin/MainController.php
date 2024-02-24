<?php

class MainController extends CAdminController {

    public function actionIndex() {
        //$this->act = array('add'=>'admin/user/add','save'=>'admin/user/save','apply'=>'admin/user/save');
        $cLog = new Log;
        $this->render('index', array(
            'gitLog' => $cLog->getGitCommits(10)
        ));
        //echo 'asd';
    }

}
