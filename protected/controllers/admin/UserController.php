<?php

class UserController extends CAdminController {

    var $defaultAction = 'index';

    //список действий
    function actionIndex() {
        $cUser = new User();
        $count = $cUser->count('user');
        $pages = new CPagination($count);
        $pages->applyLimit($cUser);
        $users = $cUser->get('user');
        $cPhone = new Phone();
        $phones = $cPhone->get('phone', array('IN', 'user_id', Helpers::getIdArray($users, 'user_id')));
        $phones = Helpers::groupBy($phones, 'user_id');

        $this->render('index', array(
            'pages' => $pages,
            'users' => $users,
            'phones' => $phones,
            'count' => $count
        ));
    }

    function actionListStopLogin() {
        $this->act->add('admin/user/addStopLogin');
        $this->act->delete("logins");
        $cStopLogin = new StopLogin();
        $pages = new CPagination($cStopLogin->count('stop_login'));
        $pages->setPageSize(100);
        $this->render('editStopLogins', array(
            'logins' => $cStopLogin->get('stop_login'),
            'pages' => $pages
        ));
    }

    function actionAddStopLogin() {
        $this->act->save("stopLogin");
        $this->render('addStopLogin');
    }

    function actionInsertStopLogin() {
        Helpers::required($_POST, array('logins'));
        $logins = explode("\n", $_POST['logins']);
        if ($logins) {
            $cStopLogin = new StopLogin();
            $cStopLogin->insert($logins);
        }
        $this->redirect(array('admin/user/listStopLogin'));
    }

    function actionRemoveStopLogin($id) {
        $id = (int) $id;
        $cStopLogin = new StopLogin();
        $cStopLogin->delete($id);
        $this->redirect(array('admin/user/listStopLogin'));
    }

    function actionStopLogin() {
        Helpers::required($_POST, array('action', 'items'));
        if ($_POST['action'] == 'delete' && !empty($_POST['items'])) {
            $ids = array_keys($_POST['items']);
            $cStopLogin = new StopLogin();
            $cStopLogin->delete($ids);
        }
        $this->redirect(array('admin/user/listStopLogin'));
    }
    
    function actionEdit($user_id){
        $userId = (int)$user_id;
        $cUser = new User;
        $user = $cUser->getByUser($userId);
        if (!$user || $userId == 1){
            throw new CHttpException(400,"Неверный ИД пользователя");
        }
        
        $cCity = new City();
        $citiesAll = $cCity->getAll();
        $citiesAll = Helpers::simplify($citiesAll, "city_id", "name");
        
        $cLog = new Log();
        $cLog->order = "time DESC";
        $cLog->limit = 1000;
        $log = $cLog->get(NULL, NULL, NULL , $userId);
        
        $this->render("edit",array(
            "user"=>$user,
            "cities" => $citiesAll,
            "phones" => Phone::getByUser($userId),
            "log" => $log
        ));
    }
    
    function actionSave($user_id){
        
    }
    
    public function actionAdd(){
        $cCity = new City();
        $citiesAll = $cCity->getAll();
        $citiesAll = Helpers::simplify($citiesAll, "city_id", "name");
        
        $this->render("add",array(
            "cities" => $citiesAll,
        ));
    }
    
    public function actionInsert(){
        $cUser = new User("add");
        $cEmail = new Email('edit'); 
        
        if (isset($_POST['email']))
            $cUser->attributes = $_POST['user'];
        
        if (isset($_POST['email']))
            $cEmail->attributes = $_POST['email'];
        if (isset($_POST["admin_level"]))
            $cUser->admin_level = $_POST["admin_level"];
        
        //проверяем телефоны для добавления в БД
        $phonesToAdd = array();
        if (isset($_POST['phones'])){
                $phones = explode("\n", $_POST['phones']);
                foreach ($phones as $phone) {
                    $phone = trim($phone);
                    if (Phone::check($phone) && !Phone::exists($phone)){
                        $phonesToAdd[]= $phone;
                    }
                }
            }
        
        if ($cUser->validate() && $cEmail->validate() && (!empty($_POST['user']['email']) || $phonesToAdd)) {
            
            //сохраняем изменения юзера
            $userId = $cUser->insert();
            Log::admin("user/insert", array("user_id"=>$userId,"admin_level"=>$cUser->admin_level), "Создан пользователь");
            
            //сохраняем параметры рассылки
            $cEmail->updateSubscription($userId);
            Log::admin("email/subscription", array("user_id"=>$userId), "Обновлена подписка пользователя на рассылки");
            
            //добавляем телефоны
            $i=0;
            foreach ($phonesToAdd as $phone) {
                    Phone::add($userId, $phone);
                    Log::admin("phone/add", array("user_id"=>$userId), "Пользователю присвоен номер телефона");
            }
            
            $this->redirect(array('admin/user'));
        } else {
            $this->model = $cUser;
            $this->actionAdd();
        }
    }

}
