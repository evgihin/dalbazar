<?php
/* @var $this AdvertController */
/* @var $advertId integer */
?>
<h1>Спасибо! Ваше объявление успешно принято.</h1>
<a href="<?= $this->createUrl('advert/show',array('advert_id'=>$advertId)) ?>">Посмотреть объявление</a><br>
<a href="<?= $this->createUrl('advert/add') ?>">Добавить еще одно объявление</a><br>
<a href="/">На главную страницу</a><br>

