<?php
/**
 * @var $user array
 * @var $cities array список городов сайта в формате id=>город
 * @var $phones array Список телефонов пользователя
 */
$this->breadcrumbs = array(
    'Пользователи'=>'admin/user',
);
        
$cities = array_merge(array("0" => "(не указано)"), $cities);
Yii::app()->clientScript->registerScriptFile('/js/jquery.validate.min.js');
$this->act
        ->save("edituser")
        ->close("admin/user")
        ->delete("edituser");
?>
<form id="edituser" method="post" action="<?= $this->createUrl('admin/user/save',array("user_id"=>$user['user_id'])) ?>">
        <h1>Редактирование пользователя <?= $user['login'] ?>.</h1>
        
        <h3>Личные данные:</h3>
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
            <?= CHtml::dropDownList("user[city]", $user['city_id'], $cities) ?>
        </div>


        <h3>Телефоны: <?= Helpers::htmlTooltip("Телефоны указаны в целях ознакомдения. Функции добавления и удаления пока отключены") ?></h3>
        <?php
        $phoneCount = count($phones);
        foreach ($phones as $phone) {
            ?>
            <?= $phone["phone"] ?>
                <span class="btn_delete_inactive" title="Удаление и добавление телефонов из админки пока отключено" ></span>
            <br>
            <?php
        }
        ?>


        <h3>E-mail подписки:</h3>
        <?= CHtml::checkBox('email[notifications]', Yii::app()->user->getState("recieve_email_notifications"), array('id' => 'email_notifications')) ?>
        <label for="email_notifications">Получать системные уведомления о своих объявлениях</label><br>

        <?= CHtml::checkBox('email[news]', Yii::app()->user->getState("recieve_email_news"), array('id' => 'email_news')) ?>
        <label for="email_news">Получать новости сайта. Обещаем писать редко =)</label><br>



    <?php if (!empty($this->errors)) { ?>
        <div class="row red"><?= Helpers::errorsToText($this->errors); ?></div>
    <?php } ?>
        
    <div class="row">
        <input type="submit" value="Сохранить">
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

<?php
if (Yii::app()->user->id==1 && !empty($log)):
?>

<h3>Действия пользователя:</h3>
<table style="width: 100%">
    <tr>
        <th>#</th>
        <th>путь</th>
        <th>действие</th>
        <th>параметры</th>
        <th>время</th>
    </tr>
<?php
foreach ($log as $logItem) { ?>
    <tr class="<?= Helpers::odder() ?>">
        <td><?= $logItem['log_id'] ?></td>
        <td><?= implode("/", array($logItem['path'], $logItem['model'], $logItem['action'])) ?></td>
        <td><?= $logItem['description'] ?></td>
        <td><?php 
        foreach(unserialize($logItem['params']) as $id=>$param){
            echo $id.' : '.$param."; ";
        }
                    ?></td>
        <td><?= date("H:i:s d.m.Y", $logItem['time']) ?></td>
    </tr>
<?php
} ?>
</table>
<?php endif; ?>