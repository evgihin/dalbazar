<?php

/**
 * Класс валидатора, при котором
 */
abstract class CArrayValidator extends CValidator {

    public $allowArray = false; //позволить проверять массивы параметров
    public $defaultMessage = "Параметр указан неверно";

    protected function validateAttribute($object, $attribute) {
        $error = false;
        // extract the attribute value from it's model object
        $value = $object->$attribute;
        if ($this->allowArray && is_array($value)) {
            foreach ($value as $val) {
                if (empty($val))
                    continue;
                if (!$this->validateValue($val)) {
                    $error = true;
                    break;
                }
            }
        } else
        if (!empty($value))
            if (!$this->validateValue($value)) {
                $error = true;
            }

        if ($error) {
            $message = $this->message !== null ? $this->message : $this->defaultMessage;
            $this->addError($object, $attribute, $message);
        }
    }

    abstract public function validateValue($val);
}

?>
