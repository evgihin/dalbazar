<?php
/* @var $user array */
/* @var $id int */
/* @var $dongle string */
/* @var $cities array список городов сайта в формате id=>город */

$cities = array_merge(array("0" => "(не указано)"), $cities);
Yii::app()->clientScript->registerScriptFile('/js/jquery.validate.min.js');
?>
<form id="edituser" method="post" action="<?= $this->createUrl('panel/user/save') ?>">
    <div class="left" style="width:350px;">
        <h1>Личные данные:</h1>

        <div class="row">
            <label for="login">Логин:</label>
            <input type="text" id="login" name="user[login]" value="<?= $user['login'] ?>">
        </div>

        <div class="row">
            <label for="lastname">Фамилия:</label>
            <input type="text" id="lastname" name="user[lastname]" value="<?= $user['lastname'] ?>">
        </div>

        <div class="row">
            <label for="name">Имя:</label>
            <input type="text" id="name" name="user[name]" value="<?= $user['name'] ?>">
        </div>

        <div class="row">
            <label for="middlename">Отчество:</label>
            <input type="text" id="middlename" name="user[middlename]" value="<?= $user['middlename'] ?>">
        </div>

        <div class="row">
            <label for="email">e-mail:</label>
            <input type="text" id="email" name="user[email]" value="<?= $user['email'] ?>"><?= Helpers::htmlTooltip("Убедитесь, что указываете свой почтовый ящик. Владелец ящика имеет доступ к Вашему аккаунту на сайте") ?>
        </div>

        <div class="row">
            <label for="email">Город:</label>
            <?= CHtml::dropDownList("user[city]", Yii::app()->user->getState('city_id'), $cities) ?>
        </div>

    </div>



    <div class="left" style="width: 230px;">
        <h1>Телефоны:</h1>
        <?php
        $phoneCount = count($phones);
        foreach ($phones as $phone) {
            ?>
            <?= $phone["phone"] ?>
            <?php if ($phoneCount > 1): ?>
                <a class="btn_delete" title="Удалить телефон" href="<?= $this->createUrl("panel/user/deletePhone", array('phoneId' => $phone["phone_id"])) ?>"></a>
            <?php else: ?>
                <span class="btn_delete_inactive" title="Нельзя удалить единственный телефон" ></span>
            <?php endIf; ?>
            <br>
            <?php
        }
        ?>
        <a href="<?= $this->createUrl("panel/user/addPhone") ?>" class="button">Добавить телефон</a>
    </div>




    <div class="left" style="width:300px;">
        <h1>E-mail подписки:</h1>
        <?= CHtml::checkBox('email[notifications]', Yii::app()->user->getState("recieve_email_notifications"), array('id' => 'email_notifications')) ?>
        <label for="email_notifications">Получать системные уведомления о своих объявлениях</label><br>

        <?= CHtml::checkBox('email[news]', Yii::app()->user->getState("recieve_email_news"), array('id' => 'email_news')) ?>
        <label for="email_news">Получать новости сайта. Обещаем писать редко =)</label><br>
    </div>
    <div class="clear"></div>



    <?php if (!empty($this->errors)) { ?>
        <div class="row red"><?= Helpers::errorsToText($this->errors); ?></div>
    <?php } ?>



    <div class="row">
        <input type="submit" value="Сохранить">
        <?php
        if ($dongle)
            $editPass = $this->createUrl("panel/user/editpassfast", array('dongle' => $dongle));
        else
            $editPass = $this->createUrl("panel/user/editpass");
        ?>
        <a href="<?= $editPass ?>" class="button">Сменить пароль</a>
    </div>
</form>
<script>
        $('#edituser').validate({
            errorPlacement: function(er, el) {
                el.parent().find(">*:last").after(er);
            },
            errorElement: "div",
            errorClass: "invalid red",
            validClass: "valid",
            rules: {
                "user[login]": {
                    required: false,
                    regexp: <?= User::loginRegExp(true) ?>,
                    remote: {
                        url: "<?= $this->createUrl('user/check') ?>",
                        type: 'get',
                        data: {login: function() {
                                return $("#login").val();
                            }
                        }
                    }
                },
                "user[lastname]": {
                    required: false,
                    regexp: <?= User::nameRegExp(true) ?>,
                },
                "user[name]": {
                    required: false,
                    regexp: <?= User::nameRegExp(true) ?>,
                },
                "user[middlename]": {
                    required: false,
                    regexp: <?= User::nameRegExp(true) ?>,
                },
                "user[email]": {
                    required: true,
                    regexp: <?= Email::regExp(true) ?>,
                },
            },
            messages: {
                "user[login]": "Логин уже занят",
            }
        });
</script>