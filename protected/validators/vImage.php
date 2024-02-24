<?php

class vImage extends CValidator {

    private $regImage = "/^[a-z0-9]+\.(jpg|png|gif|jpeg)$/iu"; //регулярка проверки имени картинки (все фотки должны быть проименованы md5 ф-ей)
    public $owner = NULL; //ИД владельца картинки. Если указан, проверяем
    public $allowEmpty = true;

    /**
     * Проверяет имя картинки регулярным выражением, которое передано там же в парамтетрах
     */
    protected function validateAttribute($object, $attribute) {
        
        if ($this->allowEmpty && empty($object->$attribute)){
            return;
        }
        
        $error = false;
        // extract the attribute value from it's model object
        $value = $object->$attribute;
        if (is_array($value)) {
            foreach ($value as $val) {
                if (!$this->_checkSingleImage($val))
                    $error = true;
            }
        } else {
            if (!$this->_checkSingleImage($value)) {
                $error = true;
            }
        }

        if ($error) {
            $message = $this->message !== null ? $this->message : 'Изображение не указано, либо указано неверно';
            $this->addError($object, $attribute, $message);
        }
    }

    private function _checkSingleImage($image) {

        if (!preg_match($this->regImage, $image)) {
            return false;
        }
        if ($this->owner !== NULL) {
            $image = new Image();
            if (!$image->checkPerms($image, $this->owner)) {
                return false;
            }
        }
        return true;
    }

}

?>
