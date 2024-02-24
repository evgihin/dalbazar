<?php
/* @var $this AdminController */
$id = $category['category_id'];
$this->act
        ->close('admin/category/list')
        ->save('editCategory');

?>
<h1>Редактирование категории <?= $category['name'] ?></h1>
<form method="post" id="editCategory" action="<?= $this->createUrl('admin/category/update', array('category_id' => $id)) ?>">
	<?= CHtml::label('Алиас:', 'alias') ?>
	<?= CHtml::textField('cat[alias]', $category['alias'], array('id' => 'alias')) ?>
	<br>
	<?= CHtml::label('Имя категории:', 'name') ?>
	<?= CHtml::textField('cat[name]', $category['name'], array('id' => 'name')) ?>
	<br>
	<?= CHtml::label('Шаблон категории:', 'flypage') ?>
	<?= CHtml::dropDownList('cat[flypage]', $category['flypage'], Flypage::getList(), array('id' => 'flypage')) ?>
	<br>
</form>
