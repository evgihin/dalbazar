<?php

class User extends CExtFormModel {

    public $lastname;
    public $name;
    public $middlename;
    public $email;
    public $login="";
    public $city = NULL;
    public $admin_level = 0;
    private $_id;
    private $_login;
    private $_presistentStates = array();
    
    public static function loginRegExp($javaScript = false) {
        $exp = "/^[a-zA-Zа-яА-Я0-9_\.\-]{4,31}$/";
        if (!$javaScript)
            $exp.="u";
        return $exp;
    }
    
    //Регулярка для фамилии имени и отчества
    public static function nameRegExp($javaScript=false){
        $exp = "/^[a-zA-Zа-яА-Я\-]{3,31}$/";
        if (!$javaScript)
            $exp.="u";
        return $exp;
    }

    function getByUser($userId) {
        $res = Yii::app()->db->createCommand('SELECT * FROM user WHERE user_id=:uid')->queryRow(true, array(':uid' => $userId));
        //если не задан пароль для входа, считать учетку упрощенной
        if (empty($res["pass"])) { 
            $res['light']=true;
            $res['simple']=false;
        } else {
            $res['light']=false;
            $res['simple']=true;
        }
        return $res;
    }

    //получить пользователя по номеру телефона
    function getByPhone($phone) {
        $phone = Phone::simplify($phone);
        return Yii::app()->db->createCommand()
                        ->select("user.*")
                        ->from("user")
                        ->join("phone", "user.user_id = phone.user_id")
                        ->where("phone_digits=:p", array(":p" => $phone))
                        ->limit(1)
                        ->queryRow(true);
    }
    
    function getByEmail($email){
        return Yii::app()->db->createCommand()
                        ->select("user.*")
                        ->from("user")
                        ->where("user.email=:e", array(":e" => $email))
                        ->limit(1)
                        ->queryRow();
    }
    
    function getByLogin($login){
        return Yii::app()->db->createCommand()
                        ->select("user.*")
                        ->from("user")
                        ->where("user.login=:e", array(":e" => $login))
                        ->limit(1)
                        ->queryRow();
    }
    
    /**
     * Проверяет занят ли логин пользователя
     * @param string $login Логин пользователя
     * @return bool true если логин занят
     */
    function checkLogin($login){
        $c = Yii::app()->db->createCommand()
                ->select('login')
                ->from('user')
                ->where('login=:log', array(':log' => $login))
                ->union("SELECT login FROM stop_login WHERE login=:log")
                ->query()->count();
        return $c>0;
    }

    function getId(){
        return $this->_id;
    }
    
    function getPersistentStates(){
        return $this->_presistentStates;
    }
    
    function getName(){
        return $this->_login;
    }
    
    function setId($id){
        $this->_id = $id;
    }
    
    function setPersistentStates($states){
        $this->_presistentStates = $states;
    }
    
    //@todo добавить обновление состояния пользователя в куках при изменении состояния из любого класса
    
    function setName($name){
       $this->_login = $name;
    }
    
    function login($userId) {
        if (Yii::app()->user->isGuest && $user = $this->getByUser($userId)) {
            $this->_id = $userId;
            $this->_login = $user["login"];
            $this->_presistentStates = $user;
            Yii::app()->user->login($this);
            return true;
        }
        else
            return false;
    }
    
    function setPassword($userId,$pass){
        Yii::app()->db->createCommand()
                ->update("user", array("pass"=>  Helpers::hash($pass)), "user.user_id=:uid", array(":uid"=>$userId));
    }
    
    function logout($destroySession=true){
        Yii::app()->user->logout($destroySession);
    }
    
    //создать юзера, возвращает созданный ИД
    function create($password=''){
        Yii::app()->db->createCommand()
                ->insert('user', array(
                    'lastname' => $this->lastname,
                    'name' => $this->name,
                    'middlename' => $this->middlename,
                    'email' => $this->email,
                    'login' => $this->getName(),
                    'pass' => Helpers::hash($password)
                ));
        return Yii::app()->db->lastInsertID;
    }

    function rules() {
        return array(
            array('login', 'application.validators.vLogin', 'on' => 'edit, add', 'message' => 'Логин не верен'),
            array('login', 'application.validators.vLoginBusy', 'on' => 'edit, add', 'message' => 'Логин уже занят'),
            array('name', 'application.validators.vName', 'on' => 'edit, add', 'message' => 'Имя не верно'),
            array('lastname', 'application.validators.vName', 'on' => 'edit, add', 'message' => 'Фамилия не верна'),
            array('middlename', 'application.validators.vName', 'on' => 'edit, add', 'message' => 'Отчество не верно'),
            array('email', 'email', 'on' => 'edit, add'),
            array('city', 'application.validators.vCity', 'on' => 'edit, add', 'message'=>"Нет такого города"),
            array('admin_level', 'unsafe'),
            array('admin_level', 'numerical', 'integerOnly'=>true),
        );
    }

    function save() {
        if (!$this->city) $this->city = NULL;
        Yii::app()->db->createCommand()
                ->update('user', array(
                    'login' => $this->login,
                    'lastname' => $this->lastname,
                    'name' => $this->name,
                    'middlename' => $this->middlename,
                    'email' => $this->email,
                    'city_id' => $this->city,
                    'admin_level' => $this->admin_level,
                        ), 'user_id=:uid', array(':uid' => Yii::app()->user->id));

        Yii::app()->user->setState('login', $this->login);
        Yii::app()->user->setState('lastname', $this->lastname);
        Yii::app()->user->setState('name', $this->name);
        Yii::app()->user->setState('middlename', $this->middlename);
        Yii::app()->user->setState('email', $this->email);
        Yii::app()->user->setState('city_id', $this->city);
    }
    
    function insert(){
        if (!$this->city) $this->city = NULL;
        Yii::app()->db->createCommand()
                ->insert("user", array(
                    'login' => $this->login,
                    'lastname' => $this->lastname,
                    'name' => $this->name,
                    'middlename' => $this->middlename,
                    'email' => $this->email,
                    'city_id' => $this->city,
                    'admin_level' => $this->admin_level,
                        ));
        return Yii::app()->db->lastInsertID;
    }

}

