<?php

/** @var CatalogController $this */
/** @var array $treepath */

$treepath = array("0"=>"(нет родителя)")+$treepath;

$this->act->save("addCatalog");
$this->act->extendSubmit("saveAndInsert", "addCatalog", "Сохранить<br>и добавить", "images/theme/icon-32-save-new.png");
$this->act->close("admin/catalog/tree");


Yii::app()->clientScript->registerScriptFile("js/chosen-1.1.0/chosen.jquery.min.js"); 
Yii::app()->clientScript->registerCssFile("js/chosen-1.1.0/chosen.min.css"); 
?>
<h1>Добавление категории каталога</h1>
<?php if ($this->model->hasErrors())
    echo '<div class="red">'.Helpers::errorsToText($this->model->getErrors()).'</div>';
?>
<form action="<?= $this->createUrl("admin/catalog/insert")?>" method="post" id="addCatalog">
    <label for="name">Название:</label>
    <input type="text" value="<?= $this->model->name ?>" id="name" name="name"><br>
 
    <label for="catalog_parent_id">Родительская категория:</label>
    <?=    CHtml::dropDownList("catalog_parent_id",0,$treepath,array("id"=>"catalog_parent_id")); ?><br>
    
    <label for="pos">Позиция:</label>
    <input type="text" value="0" id="pos" name="pos"><br>
</form>
<script>
    $("select").chosen({
        "search_contains":true
    });
    </script>