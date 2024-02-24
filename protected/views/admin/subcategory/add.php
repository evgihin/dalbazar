<?php
/* @var $this AdminController */
/* @var $categories Category */
/* @var $parents Array */
$this->act
        ->close($link)
        ->save($formId)
        ->extendSubmit('saveAdd', 'editCategory', 'сохранить и добавить', '/images/theme/icon-32-save-new.png');
        
CHtml::$errorCss = 'invalid';
?>
<h1>Создание подкатегории</h1>
<form method="post" id="editCategory" action="<?= $this->createUrl('admin/subcategory/insert') ?>">
	<?= CHtml::activeLabel($category, 'alias') ?>
	<?= CHtml::activeTextField($category, 'alias', array('size' => 50)) ?>
	<?= Helpers::htmlTooltip("Короткое имя категории для отображения в адресной строке"); ?>
	<?= CHtml::error($category, 'alias') ?>
	<br>
	<?= CHtml::activeLabel($category, 'name') ?>
	<?= CHtml::activeTextField($category, 'name', array('size' => 50)) ?>
	<?= CHtml::error($category, 'name') ?>
	<br>
	<?= CHtml::activeLabel($category, 'category_parent_id') ?>
	<?= UCHtml::activeDropDownList($category, 'category_parent_id', $parents) ?>
	<?= CHtml::error($category, 'category_parent_id') ?>
	<br>
	<?= CHtml::activeLabel($category, 'flypage') ?>
	<?= CHtml::activeDropDownList($category, 'flypage', Flypage::getList()) ?>
	<br>
</form>
