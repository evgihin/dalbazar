<?php

/** @var CatalogController $this */
/** @var array $catalog */
/** @var array $treepath */

$treepath = array("0"=>"(нет родителя)")+$treepath;

$this->act->save("editCatalog");
$this->act->close("admin/catalog/tree");
Yii::app()->clientScript->registerScriptFile("js/chosen-1.1.0/chosen.jquery.min.js"); 
Yii::app()->clientScript->registerCssFile("js/chosen-1.1.0/chosen.min.css"); 
?>
<h1>Редактирование категории "<?= $catalog['name'] ?>"</h1>
<form action="<?= $this->createUrl("admin/catalog/save",array("catalog_id"=>$catalog['catalog_id']))?>" method="post" id="editCatalog">
    <label for="name">Название:</label>
    <input type="text" value="<?= $catalog['name'] ?>" id="name" name="name"><br>
 
    <label for="catalog_parent_id">Родительская категория:</label>
    <?=    CHtml::dropDownList("catalog_parent_id",$catalog['catalog_parent_id'],$treepath,array("id"=>"catalog_parent_id")); ?><br>
    
    <label for="pos">Позиция:</label>
    <input type="text" value="<?= $catalog['pos'] ?>" id="pos" name="pos"><br>
</form>
<script>
    $("select").chosen({
        "search_contains":true
    });
    </script>