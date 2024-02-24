<?php
/* @var $email Email класс отправляемого сообщения */
/* @var $bugId int ИД добавленного бага*/
$email->subject = "Обнаружен новый баг на сайте dalbazar.ru";
$link = $this->createAbsoluteUrl("admin/bug/view", array("id"=>$bugId));
?>
Добрый день, на сайте dalbazar.ru обнаружен новый баг.<br>
Посмотреть его можно тут: <a href="<?= $link ?>"><?= $link ?></a>