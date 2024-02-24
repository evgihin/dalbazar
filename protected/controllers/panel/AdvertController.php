<?php

class AdvertController extends CPanelController {

    protected $_errors;

    public function actionIndex() {
        $cAdvert = new Advert();
        $cAdvert->onlyActive = false;
        $cAdvert->order("create_time", "DESC");
        $this->render('adverts', array(
            'adverts' => $cAdvert->getByUserFull(Yii::app()->user->id),
        ));
    }

    public function actionDelete($advert_id) {
        if (!Helpers::checkId($advert_id))
            throw new CHttpException(400, "Неверно указан ИД объявления");
        $cAdvert = new Advert();
        $cAdvert->onlyActive = FALSE;
        if (!$cAdvert->checkAvailability($advertId))
            throw new CHttpException(400, 'Неверно задан ИД объявления, либо объявление не Ваше');
        $cAdvert->delete($advertId, 'Удалено хозяином из панели управления');
        $this->redirect(array('panel/advert/index'));
    }

    public function actionEdit($advert_id) {
        if (!Helpers::checkId($advert_id))
            throw new CHttpException(400, "Неверно указан ИД объявления");
        $advert = new Advert();
        $advert->onlyActive = false;
        $image = new Image();
        $city = new City();
        $category = new Category();

        $advertInfo = $advert->getByAdvertFull($advert_id);
        if (!$advertInfo || $advertInfo['user_id'] != Yii::app()->user->id)
            throw new CHttpException(400, "Такого объявления не существует");

        $this->render('editAdvert', array(
            'categoriesLevel1' => $category->getAllLevel1(),
            'categoriesLevel2' => Helpers::toAssoc($category->getAllLevel2(), 'category_parent_id'),
            'advert' => $advertInfo,
            'imagesOld' => $image->getByAdvert($advert_id),
            'imagesTemp' => $image->getTemp(),
            'cities' => $city->getAll(),
            'id' => $advert_id
        ));
    }

    public function actionUpdate($advert_id) {
        if (!Helpers::checkId($advert_id))
            throw new CHttpException(400, "Неверно указан ИД объявления");
        $advert = new Advert("edit");
        $advertInfo = $advert->getByAdvert($advert_id);
        if (!$advertInfo || $advertInfo['user_id'] != Yii::app()->user->id)
            throw new CHttpException(400, "Такого объявления не существует");


        if (!isset($_POST['update']))
            throw new CHttpException(400, "Не указаны данные добавляемого объявления");
        $advert->attributes = $_POST['update'];

        if (!$advert->validate()) {
            $this->_errors = $advert;
            $this->actionEdit($advert_id);
            return;
        }
// обновляем объявление
        $advert->update($advert_id);
        $this->redirect(array('panel/advert/index', 'advert_id' => $advert_id));
    }

    public function actionDeleteOldImage() {
        $image = new Image();
        if (!isset($_POST['image'])) {
            echo CJSON::encode(array("error" => "Не указана фотография", 'success' => false));
            return;
        }

        $file = basename($_POST['image']);
        if (!$image->checkPerms($file, Yii::app()->user->id)) {
            echo CJSON::encode(array("error" => "Неверно указана фотография", 'success' => false));
            return;
        }
        $image->remove($file);
        echo CJSON::encode(array('success' => true));
    }

}

