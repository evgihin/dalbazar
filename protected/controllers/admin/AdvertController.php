<?php

class AdvertController extends CAdminController {

    var $_model = Null;

    public function actionList() {
        $advert = new Advert();
        $adverts = $advert->get();
        $this->render('list', array(
            'adverts' => $adverts,
        ));
    }
    
    public function actionUnpublic($advert_id){
        Helpers::requiredId($advert_id);
        State::update($advert_id, 'waited', 'Возвращено на этап модерации из админ-панели');
        Log::add("admin/advert/unpublic",array("advert_id"=>$advert_id), "Объявление удалено с публикации");
        $this->redirect(array('admin/advert/list'));
    }
    
    /*
     * Показываем все ожидающие объявления
     */
    public function actionWaited(){
        $cAdvert = new Advert();
        $cAdvert->onlyActive = false;
        
        $pages = new CPagination(10000);
        $pages->pageSize = 100;
        $pages->applyLimit($cAdvert);
        $items = $cAdvert->getByState('waited');
        $pages->setItemCount($cAdvert->count());
        
        $this->render('waited',array(
            'pages'=>$pages,
            'items'=>$items,
        ));
    }
    
    /**
     * Принять объявление
     */
    public function actionAccept($advert_id){
        State::set($advert_id, "published", "Допущено через админ-панель пользователем '".Yii::app()->user->getName()."'");
        Log::add("admin/advert/accept",array("advert_id"=>$advert_id), "Запрос модерации подтвержден");
        $this->redirect(array('admin/advert/waited'));
    }
    
    /**
     * Запретить объявление
     * @param type $advertId
     */
    public function actionReject($advert_id){
        State::set($advert_id, "rejected", "Запрещено к публикации через админ-панель пользователем '".Yii::app()->user->getName()."'");
        Log::add("admin/advert/reject",array("advert_id"=>$advert_id), "Запрос модерации отклонен");
        $this->redirect(array('admin/advert/waited'));
    }

}

?>