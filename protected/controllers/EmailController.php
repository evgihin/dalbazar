<?php

class EmailController extends CFrontEndController {

    public function actionIndex() {
        
    }

    public function actionUnsubscribe($email, $code) {
        $vEmail = new CEmailValidator();
        $cUser = new User();
        if (!$vEmail->validateValue($email) || md5($email . "fuckingmailsecretword!!!...") != $code){
            throw new CHttpException(400, "Неверный e-mail адрес");
        }
        
        if (!($user = $cUser->getByEmail($email)) || (!$user['recieve_email_news'] && !$user['recieve_email_notifications']))
            $this->render("nothing_to_unsucscribe");
        else
            $this->render("unsucscribe", array(
                "user" => $user,
                "code" => $code
            ));
    }

    public function actionCompleteUnsubscribe($email, $code) {
        $vEmail = new CEmailValidator();
        $cUser = new User();
        if (!$vEmail->validateValue($email) || md5($email . "fuckingmailsecretword!!!...") != $code || !($user = $cUser->getByEmail($email))){
            throw new CHttpException(400, "Неверный e-mail адрес");
        }
        
        $cEmail = new Email();
        
        if (!empty($_POST['unsucscribe']) && is_array($_POST['unsucscribe'])){
            if (in_array("notifications", $_POST['unsucscribe'])){
                $cEmail->unsucscribeNotifications($email);
            }
            if (in_array("news", $_POST['unsucscribe'])){
                $cEmail->unsucscribeNews($email);
            }
        }
        
        $this->render("unsubscribe_success");
    }

}