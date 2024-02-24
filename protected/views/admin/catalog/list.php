<?php
/** @var CatalogController $this */
/** @var array $items */
/** @var array $path */
Yii::app()->clientScript->registerScriptFile("/js/jquery-ui-1.9.1.custom.min.js");
Yii::app()->clientScript->registerScriptFile("/js/jquery.tree/jquery.tree.min.js");
Yii::app()->clientScript->registerCssFile("/js/jquery.tree/jquery.tree.min.css");

function displayTree(&$pathItem, &$items, &$obj) {
    foreach ($pathItem as $id => $item) {
        if (!isset($items[$id]))
            continue;
        ?><ul>
            <li><span><?= $items[$id]['name'] ?></span><a href="<?=
                                                                  $obj->createUrl("admin/catalog/edit", array("catalog_id" => $id))
                                                                  ?>" title="редактировать"><img src="/images/theme/edit.png"></a><a class="remove" href="<?=
                                                                  $obj->createUrl("admin/catalog/remove", array("catalog_id" => $id))
                                                                  ?>" title="удалить"><img src="/images/theme/icon-16-trash.png"></a></li>
        <?php displayTree($item, $items, $obj) ?>
        </ul> <?php
    }
}

$this->act->add("admin/catalog/add");
$this->act->extendLink("gotocart", "admin/catalog/cart", "корзина", "images/theme/icon-32-banner-tracks.png");
?>
<h1>Дерево каталога</h1>
<div id="treetable">
<?php displayTree($path[0], $items, $this); ?>
</div>

<script type="text/javascript">
    $(function() {
        $(".remove").click(function() {
            return confirm("Все подкатегории и связанные фирмы каталога будут удалены. Вы уверены что хотите сделать это?");
        })
</script>