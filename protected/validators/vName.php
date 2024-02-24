<?php

class vName extends CArrayValidator {

    public function validateValue($val) {
        if (!preg_match(User::nameRegExp(), $val)) {
            return false;
        }
        return true;
    }

}

?>
