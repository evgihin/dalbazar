<?php
/**  @var int $num */
/**  @var int $farpostCategoryId */
?>
<h1>Заполнение базы фильтров Farpost</h1>
Заполнение прошло успешно.<br>
Заполнено: <b><?= $num ?></b> фильтров.<br>
<a href="<?= $this->createUrl("admin/farpost/filters",array("farpost_category_id"=>$farpostCategoryId)) ?>">Посмотреть созданные фильтры</a><br>
<a href="<?= $this->createUrl("admin/farpost/listParseAdvert") ?>">Отпарсенные объявления</a><br>


