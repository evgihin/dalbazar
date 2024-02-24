<?php
/* @var $this AdminController */
$this->act
        ->close('admin/category/list')
        ->save('editCategory');

CHtml::$errorCss = 'invalid';
?>
<h1>Создание категории</h1>
<form method="post" id="editCategory" action="<?= $this->createUrl('admin/category/insert') ?>">
    <?= CHtml::activeLabel($category, 'alias') ?>
    <?= CHtml::activeTextField($category, 'alias', array('size' => 50)) ?>
    <?= Helpers::htmlTooltip("Короткое имя категории для отображения в адресной строке"); ?>
    <?= CHtml::error($category, 'alias') ?>
    <br>
    <?= CHtml::activeLabel($category, 'name') ?>
    <?= CHtml::activeTextField($category, 'name', array('size' => 50)) ?>
    <?= CHtml::error($category, 'name') ?>
    <br>
    <?= CHtml::activeLabel($category, 'flypage') ?>
    <?= CHtml::activeDropDownList($category, 'flypage', Flypage::getList()) ?>
    <br>
</form>
