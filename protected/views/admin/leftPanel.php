<?php
/* @var $this SiteController */
?>
<div class="cornered bordered adminLeft">
	<div class="ALtitle">Навигация</div>
	<div class="ALcontent">
		<a href="<?= $this->createUrl('admin/filter/list') ?>">Фильтры</a><br>
		<a href="<?= $this->createUrl('admin/advert/waited') ?>">Объявления, ждущие модерации</a><br>
		<a href="<?= $this->createUrl('admin/category/list') ?>">Категории</a><br>
		<a href="<?= $this->createUrl('admin/subcategory/list') ?>">Подкатегории</a><br>
		<a href="<?= $this->createUrl('admin/user') ?>">Пользователи</a><br>
		<a href="<?= $this->createUrl('admin/advert/list') ?>">Управление объявлениями</a><br>
		<a href="<?= $this->createUrl('admin/email/delivery') ?>">E-mail рассылка</a><br>
		<a href="<?= $this->createUrl('admin/parser') ?>">Парсинг</a><br>
		<a href="<?= $this->createUrl('admin/bug') ?>">Ошибки работы сайта</a><br>
		<a href="<?= $this->createUrl('admin/catalog') ?>">Каталог предприятий</a><br>
		<a href="<?= $this->createUrl('admin/log') ?>">Статистика</a><br>
		<!--
		<a href="<?= $this->createUrl('admin/banner/list') ?>">Реклама</a><br>
		<a href="<?= $this->createUrl('admin/site/fast') ?>">Быстрая панель (на будущее)</a><br>
		-->
	</div>
</div>
<div class="cornered bordered adminLeft">
	<div class="ALtitle">Служебные утилиты</div>
	<div class="ALcontent">
            <a href="https://metrika.yandex.ru/stat/dashboard/?counter_id=24495746" target="blank">Яндекс Метрика</a><br>
		<a href="http://www.dalbazar.ru/pma_adminus/">Gii генератор</a><br>
		<a href="http://www.dalbazar.ru/pma_adminus/">PhpMyAdmin</a><br>
		<a href="http://www.dalbazar.ru/afterlogic/">AfterLogic</a><br>
		<a href="https://my.firstvds.ru/">Биллинг</a><br>
		<a href="https://188.120.251.75:1500/ispmgr">ISP Manager</a>
	</div>
</div>