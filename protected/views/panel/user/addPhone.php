<?php

/* @var $this PanelController */
$this->breadcrumbs = array(
    "Личный кабинет" => "panel/site",
    "Учетная запись" => "panel/user/edit",
    "Добавить телефон" => "panel/user/addPhone",
);

        Yii::app()->clientScript->registerScriptFile("/js/jquery.maskedinput-1.3.min.js");
?>
<h1>Добавление нового телефона</h1>
<form id="addphone" method="post" action="<?= $this->createUrl('panel/user/checkCode') ?>">
    <input type="text" id="phone" name="user[phone]" value="">
    <input type="submit" value="Добавить">
</form>
<script>
    $('#phone').mask('+7 (999) 999-9999');
</script>