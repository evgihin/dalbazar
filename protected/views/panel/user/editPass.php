<?php
/* @todo добавить проверку пароля на клиенте на странице смены пароля, сейчас там ничего нет */
?>
<h3>Изменение пароля пользователя</h3>
<?php if ($error): ?>
<div class="errorMessage">Неверно введен пароль</div>
<?php endif; ?>
<form action="<?= $this->createUrl('panel/user/savepass')?>" method="post">
    <?= CHtml::label("Старый пароль:", "oldPass") ?>
    <input id="oldPass" type="password" name="oldPass"><br>
    
    <?= CHtml::label("Новый пароль:", "newPass") ?>
    <input id="newPass" type="password" name="newPass"><br>
    
    <?= CHtml::label("Подтвердите пароль:", "confirmPass") ?> 
    <input id="confirmPass" type="password" name="confirmPass"><br>
    
    <input type="submit" value="Сохранить">
    <a href="<?= $this->createUrl("panel/user/editUser") ?>">отмена</a>
</form>

