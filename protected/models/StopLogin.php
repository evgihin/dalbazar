<?php

class StopLogin extends CExtFormModel {

    function insert($logins) {
        $comm = $this->createCommand();
        Yii::import('application.validators.vLogin');
        $vLogin = new vLogin();
        if (is_string($logins)) {
            $logins = array($logins);
        }
        foreach ($logins as $login) {
            $login = trim($login);
            $comm1 = $this->createCommand();
            //если логин указан верно и его нет в списке, то добавляем
            if ($vLogin->validateValue($login) && !$comm1->select()
                            ->from('stop_login')
                            ->where('login=:l', array(':l' => $login))
                            ->query()->count()) {

                $comm->insert('stop_login', array(
                    'login' => $login
                ));
            }
        }
    }

    function delete($ids) {
        $comm = $this->createCommand();
        if (is_string($ids)) {
            $ids = array($ids);
        }
        $comm->delete('stop_login', array('IN', 'stop_login_id', $ids));
    }

}
