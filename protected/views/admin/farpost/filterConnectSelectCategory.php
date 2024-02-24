<?php
/** @var array $categories список категорий */
/** @var array $sourceCategories список категорий */
/** @var array $advertCount количество объявлений для каждой категории */
/** @var array $filterCount количество фильтров для каждой категории */
$this->breadcrumbs = array(
    "Парсинг"=>array("admin/parser"),
    "Реестр фильтров FarPost"=>array("admin/farpost/filters")
)
?>
<h1>Реестр фильтров farpost</h1>
Категория: 
<select id="category">
    <option value="0">(не указано)</option>
    <?php foreach ($categories[0] as $pCategory) { ?>
        <option value="<?= $pCategory['farpost_category_id'] ?>"><?= $pCategory['name'] ?></option>
    <?php } ?>
</select>

<?php foreach ($categories[0] as $pCategory) { ?>
    <div class="hidden detailCategory" id="detail<?= $pCategory['farpost_category_id'] ?>">
        <table>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Ссылка</th>
                <th>Связано с</th>
                <th>Фильтров</th>
                <th>Объявлений</th>
            </tr>
            <?php
            if (!empty($categories[$pCategory['farpost_category_id']]))
                foreach ($categories[$pCategory['farpost_category_id']] as $category) {
                
                if (isset($relations[$category['farpost_category_id']])) 
                    $rel = $relations[$category['farpost_category_id']]["category_id"];
                else 
                    $rel = 0;
                    ?>
                    <tr class="<?= Helpers::odder() ?>">
                        <td>
                            <?= ($rel)?CHtml::link($category['farpost_category_id'], array("admin/farpost/filters", 'farpost_category_id' => $category['farpost_category_id'])):$category['farpost_category_id'] ?>
                        </td>
                        <td>
                            <?= ($rel)?CHtml::link($category['name'], array("admin/farpost/filters", 'farpost_category_id' => $category['farpost_category_id'])):$category['name'] ?>
                        </td>
                        <td>http://farpost.ru/<?= $category['link'] ?></td>
                        <td class="very-small">
                            <?php
                            if ($rel){
                                if ($pid = $sourceCategories[$rel]['category_parent_id'])
                                    echo $sourceCategories[$pid]['name']." -> ";
                                echo $sourceCategories[$rel]['name'];
                            } else {
                                echo CHtml::link("Связать", array("admin/farpost/categorytree","farpost_category_id"=>$pCategory['farpost_category_id']));
                            }
                            ?>
                        </td>
                        <td><?= (isset($filterCount[$category['farpost_category_id']])) ? $filterCount[$category['farpost_category_id']] : 0 ?></td>
                        <td><?= (isset($advertCount[$category['farpost_category_id']])) ? $advertCount[$category['farpost_category_id']] : 0 ?></td>
                    </tr>
                <?php }
            ?>
        </table>
    </div>
    <?php
}
?>
<script>
    $("#category").change(function() {
        $("div.detailCategory").hide();
        $("#detail" + $(this).val()).show();
    });
</script>