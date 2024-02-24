<?php
/** @var $adverts array массив результатов объявлений */
/** @var $images array массив фоток объявлений */
/** @var $query string исходный запрос */
/** @var $count int количество найденных результатоы */
/** @var $pagination CPagination класс постраничной навигации  */
?>
<span class="small">найдено <?= $count ?> результат(ов)</span>
<h1>Результаты поиска по запросу "<?= $query ?>":</h1>
<?php
echo $this->renderPartial("/category/fly_default", array(
    "adverts" => $adverts,
    "images" => $images,
));
?>
<?php
$this->widget('CLinkPager', array(
    'pages' => $pagination,
    'prevPageLabel' => '←',
    'nextPageLabel' => '→',
));
?>