<?php

class vPhone extends CValidator {

    public $mobile = false; //только мобильные телефоны

    protected function validateAttribute($object, $attribute) {
        $error = false;
        // extract the attribute value from it's model object
        $value = $object->$attribute;
        if (is_array($value)) {
            foreach ($value as $val) {
                if (!$this->validateValue($val))
                    $error = true;
            }
        } elseif (!$this->validateValue($value)) {
            $error = true;
        }

        if ($error) {
            $message = $this->message !== null ? $this->message : 'Телефон указан неверно';
            $this->addError($object, $attribute, $message);
        }
    }

    public function validateValue($val) {
        if (!preg_match(Phone::regExp(), $val)) {
            return false;
        }
        if ($this->mobile) {
            //мобильные начинаются с цифр 79хх ххх-хх-хх
            $val = Phone::simplify($val);
            if ($val[0] == '7' && $val[1] = '9') {
                return false;
            }
        }
        return true;
    }

}

?>
