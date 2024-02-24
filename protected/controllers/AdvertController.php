<?php

class AdvertController extends CFrontEndController {

    public $left;
    protected $_errors;

    public function actionAdd() {
        $image = new Image();
        $city = new City();

        $this->left = $this->renderPartial("leftAdd", NULL, true);

        $category = new Category();
        $categories = $category->get(NULL, NULL, TRUE);
        $rootCategories = $category->getAllLevel1();
        $subCategories = $category->getAllLevel2();

        $shortSubCategories = array();
        foreach ($subCategories as $val) {
            $shortSubCategories[] = array(
                "parent" => $val["category_parent_id"],
                "id" => $val["category_id"],
                "name" => $val["name"],
            );
        }
        $subCategories = Helpers::toAssoc($subCategories, "category_parent_id");
        $shortSubCategories = Helpers::toAssoc($shortSubCategories, "parent");
        if (!Yii::app()->user->isGuest) {
            $phones = Phone::getByUser(Yii::app()->user->id);
        } else {
            $phones = array();
        }

        $this->render('add', array(
            'categories' => $categories,
            'rootCategories' => $rootCategories,
            'subCategories' => $subCategories,
            'shortSubCategories' => $shortSubCategories,
            'images' => $image->getTemp(),
            'cities' => $city->getAll(),
            'phones' => $phones
        ));
    }

    //отправляет сообщение и возвращает результат отправки
    public function actionCheckPhone() {
        
        Helpers::disableWebLogRoute();
        $phone = new Phone();
        $result = array(
            "remain" => 0, //сколько осталось СМС у пользователя
            "text" => "", //текст состояния
            "state" => "", //имя состояния (sended, rejected|resend, rejected|end, confirmed, notRemainMessage, error)
        );
        if (!isset($_REQUEST['phone']) || !Phone::check($_REQUEST['phone'])) {
            $result["state"] = "error";
            $result["text"] = "Телефон не указан либо указан неверно";
            echo CJSON::encode($result);
            return;
        }
        $num = $_REQUEST['phone'];

        if ($phone->requestConfirmation($num, NULL, Yii::app()->session->sessionID)) {
            $result["remain"] = $phone->reminingConfirmations($num, NULL, Yii::app()->session->sessionID) + 1;
            $result["text"] = "На номер " . $num . " было отправлено СМС с кодом подтверждения.
                Введите полученный код в поле ниже.";
            $result["state"] = "sended";
        } else {
            $result["remain"] = $phone->reminingConfirmations($num, NULL, Yii::app()->session->sessionID);
            $result["text"] = "Вы исчерпали доступные попытки. Попробуйте активировать телефон позже либо указать другой номер телефона.";
            $result["state"] = "notRemainMessage";
        }
        echo CJSON::encode($result);
    }

    public function actionCheckPhoneCode() {
        
        Helpers::disableWebLogRoute();
        $result = array(
            "remain" => 0, //сколько осталось СМС у пользователя
            "text" => "", //текст состояния
            "state" => "", //имя состояния (sended, rejected|resend, rejected|end, confirmed, notRemainMessage, error)
        );
        if (!isset($_REQUEST['phone']) || !Phone::check($_REQUEST['phone']) || !isset($_REQUEST['code'])) {
            $result["text"] = "Телефон не указан либо указан неверно";
            $result["state"] = "error";
            echo CJSON::encode($result);
            return;
        }
        if (!isset($_REQUEST['code'])) { //@todo прогнать код валидатором
            $result["text"] = "Код подтверждения не указан либо указан неверно";
            $result["state"] = "error";
            echo CJSON::encode($result);
            return;
        }
        $num = $_REQUEST['phone'];
        $code = $_REQUEST['code'];
        $phone = new Phone();
        if ($phone->checkConfirmation($num, $code, NULL, Yii::app()->session->sessionID)) {
            $result["text"] = "Ваш телефон подтвержден. Для упрощения авторизации установите пароль в личном кабинете.";
            $result["state"] = "confirmed";

            //заходим под соответствующим юзером
            $cUser = new User();
            if ($user = $cUser->getByPhone($num)) {
                //если пользователь уже есть в базе, логинимся под ним
                $cUser->login($user['user_id']);
            } else {
                //иначе создаем нового пользователя
                $id = $cUser->create();
                $cUser->login($id);
                //и привязываем к нему созданный телефон
                $phone->add($id, $num, true);
            }
        } else {

            if ($phone->requestConfirmation($num, NULL, Yii::app()->session->sessionID)) {
                $result["state"] = "rejected|resend";
                $result["remain"] = $phone->reminingConfirmations($num, NULL, Yii::app()->session->sessionID) + 1;
                $result["text"] = "СМС код неверен. Вам отправлено новое сообщение с кодом.";
            } else {
                $result["state"] = "rejected|end";
                $result["remain"] = $phone->reminingConfirmations($num, NULL, Yii::app()->session->sessionID);
                $result["text"] = "Вы исчерпали лимит отправленных СМС на сегодня. Повторите попытку позже либо укажите другой номер телефона.";
            }
        }
        echo CJSON::encode($result);
        return;
    }

    public function actionInsert() {
        if (!isset($_POST['add']))
            throw new CHttpException(400, "Не указаны данные добавляемого объявления");



        //определяем юзера
        if (Yii::app()->user->isGuest) {
            throw new CHttpException(400, "Пользователь не подтвержден либо неизвестен");
        } else {
            $advert = new Advert("add");

            //валидация
            $advert->attributes = $_POST['add'];
            $advert->user_id = (Yii::app()->user->isGuest) ? 0 : Yii::app()->user->id;
            if (!$advert->validate()) {
                $this->_errors = $advert;
                $this->actionAdd();
                return;
            } else {
                // добавляем объявление в БД
                $id = $advert->insert();

                //добавляем картинки
                if (!empty($_POST['images']) && is_array($_POST['images'])) {
                    $images = new Image("add");
                    $images->images = $_POST['images'];
                    if (!empty($_POST['mainPicture']))
                        $images->mainPicture = $_POST['mainPicture'];
                    if ($images->validate()) {
                        $images->moveTempToAdvert($images->images, $id, $images->mainPicture);
                    }
                }

                //добавляем телефоны
                $phones = new Phone();
                if (!empty($_POST['phones']) && is_array($_POST['phones']) && sizeof($_POST['phones']) <= 5) {
                    //дополнительные (временные) телефоны
                    foreach ($_POST['phones'] as $phone) {
                        if (Phone::check($phone))
                            $phones->attachToAdvert($id, $phone, true);
                    }
                }
                if (!empty($_POST['turned_phones']) && is_array($_POST['turned_phones'])) {
                    //основные телефоны
                    foreach ($_POST['turned_phones'] as $phone) {
                        if (Phone::check($phone) && $phones->checkOwner($phone))
                            $phones->attachToAdvert($id, $phone, false);
                    }
                }

                //добавляем фильтры
                //@todo перенести добавление фильтров из класса advert в класс filter
                $this->redirect(array('advert/inserted', 'advert_id' => $id));
            }
        }
    }

    public function actionInserted($advert_id) {
        $this->render('inserted', array('advertId' => $advert_id));
    }

    public function actionUpload() {
        Helpers::disableWebLogRoute();
        $image = new Image();
        if (!isset($_FILES['file'])) {
            echo CJSON::encode(array("error" => "Превышен допустимый размер фотографии."));
            return;
        }
        if ($name = $image->uploadTemp($_FILES['file'])) {
            echo CJSON::encode(array(
                "error" => "none",
                "image" => Helpers::getImageUrl($name, 120, 120),
                "name" => $name));
        } else {
            $arr = array("error" => "Изображение повреждено, либо не соответствует требованиям.");
            if (YII_DEBUG)
                $arr['debug'] = $image->image->error;
            echo CJSON::encode($arr);
        }
    }

    public function actionDeleteTempImage() {
        
        Helpers::disableWebLogRoute();
        $image = new Image();
        if (!isset($_POST['image'])) {
            echo CJSON::encode(array("error" => "Не указана фотография", 'success' => false));
            return;
        }

        $image->removeTemp(basename($_POST['image']));
        echo CJSON::encode(array('success' => true));
    }

    public function actionCleanTempImages() {
        $image = new Image();
        $image->cleanTemp();
    }

    /**
     * Отображает карточку объявления
     * @param int $advert_id
     * @throws CHttpException
     */
    public function actionShow($advert_id) {
        //throw new CHttpException(500, "<h1>Функция пока недоступна</h1>"); return;
        $advert_id = (int) $advert_id;
        if (!$advert_id)
            throw new CHttpException(400, "Неверный ИД объявления");
        $cAdvert = new Advert();
        $filter = new Filter();
        $cParam = new Param;
        $image = new Image();

        $advert_info = $cAdvert->getByAdvertFull($advert_id);
        if (!$advert_info)
            throw new CHttpException(400, 'Нет объявления с таким ИД-ом');

        Advert::registerView($advert_id);
        
        $cCategory = new Category();
        $parentCategory = $cCategory->getByCategory($advert_info['category_parent_id']);

        $images = $image->getByAdvert($advert_id);
        $this->render('template_default', array(
            'advert' => $advert_info,
            'images' => $images,
            'filters' => $cParam->getValuesFull($advert_id),
            'phones' => Phone::getByAdvert($advert_id),
            'advert_id' => $advert_id,
            'parentCategory'=>$parentCategory,
        ));
    }

    public function actionSms() {
        $this->layout = 'small';
        $this->render('smsRules');
    }

}
