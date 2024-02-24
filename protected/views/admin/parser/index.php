<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$this->breadcrumbs = array(
    "Парсинг"=>array("admin/parser")
    );
?>
<h1>Парсинг категорий сайта Дальбазар</h1>
<?= CHtml::link("Дерево категорий FarPost", array("admin/farpost/categorytree")); ?><br>
<?= CHtml::link("Реестр фильтров FarPost", array("admin/farpost/filters")); ?><br>
<?= CHtml::link("Добавить задачу парсинга", array("admin/farpost/addParseTask")); ?><br>
<?= CHtml::link("Задачи парсинга", array("admin/farpost/listParseTask")); ?><br>
<?= CHtml::link("Отпарсенные объявления", array("admin/farpost/listParseAdvert")); ?>