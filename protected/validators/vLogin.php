<?php

class vLogin extends CArrayValidator {

    public function validateValue($val) {
        if (!preg_match(User::loginRegExp(), $val)) {
            return false;
        }
        return true;
    }

}

?>
