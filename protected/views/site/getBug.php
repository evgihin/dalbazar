<?php
/* @var $this SiteController */
?>
<h1>Опубликовать отчет об ошибке</h1>
<form action="<?= $this->createUrl('site/insertBug') ?>" method="post">
	<div class="advAddField">
		<label for="link">Ссылка на страницу с ошибкой:</label>
		<input type="text" id="link" name="link" value="<?= Yii::app()->request->urlReferrer ?>" size="65">
		<?=		Helpers::htmlTooltip('Адрес страницы, на которой произошла ошибка. Можете скопировать его из адресной строки браузера.') ?>
	</div>
	<div class="advAddField">
		<label for="text">Описание ошибки:</label>
		<textarea id="text" name="text" cols="55" rows="5"></textarea>
		<?=		Helpers::htmlTooltip("Опишите обстоятельства, при которых, по Вашему мнению, произошла ошибка. В свободной форме.") ?>
	</div>
	<input type="submit" value="Отправить ошибку на рассмотрение">
</form>