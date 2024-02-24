<?php
/** @var CatalogController $this */
/** @var array $items */
/** @var array $path */
Yii::app()->clientScript->registerScriptFile("/js/jquery-ui-1.9.1.custom.min.js");

function displayTree(&$pathItem, &$items, &$obj) {
    foreach ($pathItem as $id => $item) {
        if (!isset($items[$id]))
            continue;
        ?><ul><?php
            if ($items[$id]['removed']):
                ?>
                <li>
                    <span><?= $items[$id]['name'] ?></span><!--
                    <a href="<?= $obj->createUrl("admin/catalog/edit", array("catalog_id" => $id)) ?>" title="редактировать">
                        <img src="/images/theme/edit.png">
                    </a>-->
                    <a class="recover" href="<?= $obj->createUrl("admin/catalog/recover", array("catalog_id" => $id)) ?>" title="восстановить">
                        <img src="/images/theme/check.png">
                    </a>
                </li>
            <?php else: ?>
                <li class="disabled"><span><?= $items[$id]['name'] ?></span></li>
            <?php
            endif;
            displayTree($item, $items, $obj)
            ?>
        </ul> <?php
    }
}

$this->act->add("admin/catalog/add");
$this->act->extendLink("gotolist", "admin/catalog/tree", "все элементы", "images/theme/icon-32-banner-tracks.png");
?>
<h1>Дерево каталога</h1>
<div id="treetable">
    <?php displayTree($path[0], $items, $this); ?>
</div>

<script type="text/javascript">
    $(function() {
        $(".recover").click(function() {
            return confirm("Эта категория, подкатегории и связанные предприятия станут видны для пользователя. Вы точно хотите восстановить?");
        })
    })
</script>