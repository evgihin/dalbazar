<?php

class Image extends CFormModel {

    public $images = array(); //строка рег. выр-е (имя файла)
    public $mainPicture = "";
    public $image = Null;

    public function rules() {
        return array(
            array("mainPicture", "application.validators.vImage", 'on' => 'add, edit', 'message' => 'Картинка указана в неверном формате'),
            array("images", "application.validators.vImage", 'on' => 'add, edit', 'message' => 'одна из картинок указана неверно'),
        );
    }

   

    //хранит в сессии уникальный ИД для временных картинок
    private function _getTempUniqueId() {
        if (!$key = Yii::app()->user->getState("tempImagesId")) {
            $key = md5(time() . "temp pictures");
            Yii::app()->user->setState("tempImagesId", $key);
        }
        return $key;
    }
    
    private function _loadFromUrl($image) {
        $name = "images/temp/".basename($image);
        $f = fopen($name, "wb");
        $file = file_get_contents($image);
        fwrite($f, $file);
        fclose($f);
        return $name;
    }
    
    public function addRemote($advertId,$url,$urlBig='', $primary=false){
        Yii::app()->db->createCommand()->insert('image', array(
                'advert_id' => $advertId,
                'name' => $url,
                'name_big' => $urlBig,
                'main' => 0,
                'creation' => time()
            ));
        $id = Yii::app()->db->lastInsertID;
        if ($primary)
            $this->setPrimary ($advertId, $id);
        return $id;
    }

    /**
     * Загружает временную картинку на сервер и отмечает её в базе данных
     * @param array $file элемент массива $_FILE, указывающий на картинку
     * @return mixed имя картинки если всё прошло успешно, либо false в случае ошибки.
     */
    public function uploadTemp($file) {
        Yii::app()->setComponents(array('imagemod' => array('class' => 'application.extensions.imagemodifier.CImageModifier')));
        
        if ((new CUrlValidator())->validateValue($file)){
            $file = $this->_loadFromUrl($file);
        }

        $img = Yii::app()->imagemod->load($file, 'ru_RU');
        $img->image_resize = true;
        $img->image_ratio_no_zoom_in = true;
        $img->image_x = 1280;
        $img->image_y = 1024;
        $img->jpeg_quality = 80;
        $img->file_new_name_body = md5(time() . 'time' . time());
        $img->image_max_ratio = 2;
        $img->image_min_ratio = 0.5;
        $img->image_min_width = 150;
        $img->image_min_height = 150;
        $img->image_convert = 'jpg';
        $img->process(Yii::app()->params['advertImagesStoragePath']);
        $this->image = $img;
        if (!$img->processed) { //если ресайзить и грузить не получилось
            return false;
        } else {
            $this->_addTemp($img->file_dst_name);
            return $img->file_dst_name;
        }
    }

    /**
     * Отмечает в базе данных загруженную картинку
     * @param type $image имя картинки, которую нужно отметить в базе данных
     */
    private function _addTemp($image) {
        Yii::app()->db->createCommand()
                ->insert('temp_upload_advert_image', array(
                    'image' => $image,
                    'date' => time(),
                    'unique_id' => $this->_getTempUniqueId()
        ));
    }

    /**
     * Получает временные картинки для указанной сессии
     * @param string $session_id ИД сессии. По умолчанию ИД текущей сессии
     * @return array Список имен временных картинок для указанной сессии
     */
    public function getTemp($session_id = "") {
        return Yii::app()->db->createCommand()
                        ->select('image')
                        ->from('temp_upload_advert_image')
                        ->where('unique_id=:unique_id', array(':unique_id' => $this->_getTempUniqueId()))
                        ->queryColumn();
    }

    /**
     * Чистит временные картинки для объявлений (те которые были закачаны более месяца назад)
     */
    public function cleanTemp() {
        $date = time() - Yii::app()->params['tempAdvertImagesStorageTime'];
        $result = Yii::app()->db->createCommand()
                ->select('image')
                ->from('temp_upload_advert_image')
                ->where('date<:d', array(':d' => $date))
                ->queryColumn();
        foreach ($result as $value) {
            unlink(Yii::app()->params['advertImagesStoragePath'] . $value);
        }

        Yii::app()->db->createCommand()->delete('temp_upload_advert_image', 'date<:d', array(':d' => $date));
    }

    /**
     * Удалить временные картинки загруженные при подаче объявления
     * @param mixed $name Либо имя объявления либо массив имен объявлений которые нужно удалить
     */
    public function removeTemp($name, $deleteFile = true) {
        if (!is_array($name))
            $name = array($name);
        if ($deleteFile) {
            $realimages = Yii::app()->db->createCommand()
                    ->select('image')
                    ->from('temp_upload_advert_image')
                    ->where(array('IN', 'image', $name))
                    ->queryColumn();
            foreach ($realimages as $value) {
                @unlink(Yii::app()->params['advertImagesStoragePath'] . $value);
            }
        }
        return Yii::app()->db->createCommand()
                        ->delete('temp_upload_advert_image', array('IN', 'image', $name));
    }

    public function remove($name, $deleteFile = true) {
        if (!is_array($name))
            $name = array($name);
        if ($deleteFile)
            foreach ($name as $value) {
                $value = basename($value);
                @unlink(Yii::app()->params['advertImagesStoragePath'] . $value);
            }
        Yii::app()->db->createCommand()
                ->delete('image', array('IN', 'name', $name));
    }

    public function checkPerms($name, $userId) {
        if (!is_array($name))
            $name = array($name);
        $matchCount = Yii::app()->db->createCommand()
                ->select('COUNT(*)')
                ->from('image')
                ->join('advert', 'advert.advert_id=image.advert_id')
                ->where(array('AND', array('IN', 'image.name', $name), 'advert.user_id=:uid'), array(':uid' => $userId))
                ->queryScalar();
        return $matchCount == count($name);
    }

    /**
     * Получить только загруженные на сервер картинки
     * @param array $images список картинок
     * @return array список точно загруженных картинок
     */
    public function checkTempAvailable($images) {
        return Yii::app()->db->createCommand()
                        ->select('image')
                        ->from('temp_upload_advert_image')
                        ->where(array('IN', 'image', $images))
                        ->queryColumn();
    }

    public function moveTempToAdvert($images, $advertId, $mainPicture = "") {
        if (!count($images))
            return;
        if (!$mainPicture || !in_array($mainPicture, $images))
            $mainPicture = $images[0];

        $command = Yii::app()->db->createCommand();
        $primaryId = 0;
        foreach ($images as $val) {
            $command->insert('image', array(
                'advert_id' => $advertId,
                'name' => $val,
                'main' => 0,
                'creation' => time()
            ));
            if ($mainPicture == $val)
                $primaryId = Yii::app()->db->lastInsertID;
        }
        $this->setPrimary($advertId, $primaryId);

        $this->removeTemp($images, false);
    }
    
    public function setPrimary($advertId,$imageId){
        Yii::app()->db->createCommand()->update("image", array("main"=>0), "advert_id=:aid", array(":aid"=>$advertId));
        return Yii::app()->db->createCommand()->update("image", array("main"=>1), "image_id=:iid", array(":iid"=>$imageId));
    }

    /**
     * Получить список картинок для объявления
     * @param mixed $advertId ИД объявления либо массив ИД-ов
     * @return array возвращается <b>ТОЛЬКО</b> список имён файлов. Остальная информация не возвращается
     */
    public function getByAdvert($advertId) {
        if (!is_array($advertId))
            $advertId = array($advertId);
        return Yii::app()->db->createCommand()
                        ->select('name')
                        ->from('image')
                        ->where(array('IN', 'advert_id', $advertId))
                        ->order('main DESC')
                        ->queryColumn();
    }

    /**
     * Получить список картинок и дополнительную информацию для объявления.
     * @param mixed $advertId ИД объявления либо массив ИД-ов
     * @return array полная информация о картинках
     */
    public function getByAdvertFull($advertId) {
        if (!is_array($advertId))
            $advertId = array($advertId);
        return Yii::app()->db->createCommand()
                        ->select()
                        ->from('image')
                        ->where(array('IN', 'advert_id', $advertId))
                        ->order('main DESC')
                        ->queryAll();
    }

}

;
