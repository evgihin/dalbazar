<?php
/* @var $this PanelController */
/* @var $phone  string */
$this->breadcrumbs = array(
    "Личный кабинет" => "panel/site",
    "Учетная запись" => "panel/user/edit",
    "Добавить телефон" => "panel/user/addPhone",
    "Подтверждение кода" => array("panel/user/checkCode","phone"=>$phone),
        );
?>

<h1>Подтверждение телефона</h1>
<form id="addphone" method="post" action="<?= $this->createUrl('panel/user/insertPhone') ?>">
    <input type="text" id="code" name="user[code]" value="">
    <input type="hidden" id="phone" name="user[phone]" value="<?= $phone ?>">
    <input type="submit" value="Добавить">
</form>