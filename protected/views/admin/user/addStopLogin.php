<?php
$this->breadcrumbs = array(
    'Пользователи'=>'admin/user',
    'Стоп-слова'=>'admin/user/listStopLogin',
    'Добавить' => 'admin/user/addStopLogin'
)
?>
<h1>Добавление стоп-логинов</h1>
Список логинов: (указывать каждый в новой строке)
<form action="<?= $this->createUrl('admin/user/insertStopLogin') ?>" method="POST" id="stopLogin">
    <textarea cols="70" rows="30" name="logins"></textarea>
</form>