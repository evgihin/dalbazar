<?php
/**
 * @var $pages CPagination
 * @var $users array
 * @var $phones array
 * @var $count int
 */
$this->breadcrumbs = array(
    'Пользователи' => 'admin/user',
    'Создание пользователя' => 'admin/user/add',
        );
$this->act->save("user");

$cities = array_merge(array("0" => "(не указано)"), $cities);
Yii::app()->clientScript->registerScriptFile('/js/jquery.validate.min.js');
?>

<form id="edituser" method="post" action="<?= $this->createUrl('admin/user/insert') ?>">
        <h1>Создание нового пользователя</h1>
        
        <h3>Личные данные:</h3>
        <div class="row">
            <label for="login">Логин:</label>
            <input type="text" id="login" name="user[login]">
        </div>

        <div class="row">
            <label for="lastname">Фамилия:</label>
            <input type="text" id="lastname" name="user[lastname]">
        </div>

        <div class="row">
            <label for="name">Имя:</label>
            <input type="text" id="name" name="user[name]">
        </div>

        <div class="row">
            <label for="middlename">Отчество:</label>
            <input type="text" id="middlename" name="user[middlename]">
        </div>

        <div class="row">
            <label for="email">e-mail:</label>
            <input type="text" id="email" name="user[email]" class="req_fields">
        </div>

        <div class="row">
            <label for="email">Город:</label>
            <?= CHtml::dropDownList("user[city]", 0, $cities) ?>
        </div>
        
        <div class="row">
            <label for="admin_level">Является администратором</label>
            <?= CHtml::checkBox('admin_level', 0, array('id' => 'admin_level')) ?>
        </div>


        <div class="row">
            <label for="phones">Телефоны: <?= Helpers::htmlTooltip("Каждый телефон следует указывать с новой строки в числовом формате: <b>79141234567</b>") ?></label>
            <?= CHtml::textArea("phones", '', array( "class"=>"req_fields")) ?>
        </div>


        <h3>E-mail подписки:</h3>
        <?= CHtml::checkBox('email[notifications]', Yii::app()->user->getState("recieve_email_notifications"), array('id' => 'email_notifications')) ?>
        <label for="email_notifications">Получать системные уведомления о своих объявлениях</label><br>

        <?= CHtml::checkBox('email[news]', Yii::app()->user->getState("recieve_email_news"), array('id' => 'email_news')) ?>
        <label for="email_news">Получать новости сайта. Обещаем писать редко =)</label><br>



    <?php if (!empty($this->errors)) { ?>
        <div class="row red"><?= Helpers::errorsToText($this->model->getErrors()); ?></div>
    <?php } ?>
        
    <div class="row">
        <input type="submit" value="Сохранить">
    </div>
</form>
<script>
        $.validator.addMethod('mobilePhone', function(val) {
            var vals = val.split("\n");
            var regexp = <?= Phone::regExp(true) ?>;
            for (i in vals){
                if (vals[i]!="" && !regexp.test(vals[i]))
                    return false;
            }
            return true;
        }, "Один из телефонов указан неверно");
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
                    regexp: <?= Email::regExp(true) ?>,
                    require_from_group: [1, ".req_fields"],
                },
                "phones":{
                    require_from_group: [1, ".req_fields"],
                    mobilePhone: true,
                },
            },
            messages: {
                "user[login]": {
                    regexp: "Логин не соответствует формату",
                    remote: "Логин уже занят",
                }
            }
        });
</script>