<?php
/** @var array $farpostFilters набор фильтров */
/** @var array $originalFilters набор фильтров */
/** @var int $farpostCategoryId ид текущей категории */
/** @var array $category текущая категрия */
/** @var int $advertCount количество объявлений, доступных для этой категории */
$this->breadcrumbs = array(
    "Парсинг" => array("admin/parser"),
    "Реестр фильтров FarPost" => array("admin/farpost/filters"),
    $category['name'] => array("admin/farpost/filters", "farpost_category_id" => $farpostCategoryId)
        )
?>
<h1>Управление базой фильтров для категории "<?= $category['name'] ?>"</h1>
<?php
if ($farpostFilters):
    $this->act->save("mainform");
    $this->act->close("admin/farpost/filters");
    ?>
    <form action="<?= $this->createUrl("admin/farpost/savefilters", array("farpost_category_id" => $farpostCategoryId)) ?>" id="mainform" method="post">
        <table>
            <tr>
                <th>Фильтр Farpost</th>
                <th>Оригинальный фильтр</th>
            </tr>
        <?php
        $originalFilters = array(0=>"(выберите фильтр)") + $originalFilters;
        foreach ($farpostFilters as $farpostFilter) { ?>
            <tr class="<?= Helpers::odder(); ?>">
                <td><?= $farpostFilter['name'] ?></td>
                <td><?= CHtml::dropDownList("depending[".$farpostFilter['farpost_filter_relation_id']."]", $farpostFilter['filter_id'], $originalFilters) ?></td>
            </tr>
        <?php }
        ?>
        <a href="<?= $this->createUrl("admin/farpost/FillFilterBase", array("farpost_category_id" => $farpostCategoryId)) ?>">заполнить базу фильтров из объявлений (<?= $advertCount ?>)</a>
        </table>
    </form>
<?php else :
    ?>
    Фильтров не найдено.<br>
    Попробуйте <a href="<?= $this->createUrl("admin/farpost/FillFilterBase", array("farpost_category_id" => $farpostCategoryId)) ?>">заполнить базу фильтров из объявлений (<?= $advertCount ?>)</a>
<?php endif; ?>