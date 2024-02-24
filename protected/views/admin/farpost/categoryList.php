<?php
/* @var this FarpostController */
/* @var categories array список категорий */
/* @var sourceCategories array список категорий сайта dalbazar */
/* @var relations array список зависимостей */
/* @var farpostCategoryId array список зависимостей */
$this->breadcrumbs = array(
    "Парсинг"=>array("admin/parser"),
    "Список категорий"=>array("admin/farpost/categorytree")
)
?>
<h1>Список категорий farpost</h1>
Категория: 
<select id="category">
    <option value="0">(не указано)</option>
    <?php foreach ($categories[0] as $pCategory) { ?>
        <option value="<?= $pCategory['farpost_category_id'] ?>" <?= ($pCategory['farpost_category_id']==$farpostCategoryId)?'selected="selected"':'' ?>>
            <?= $pCategory['name'] ?>
        </option>
    <?php } ?>
</select>

<form action="<?= $this->createUrl("admin/farpost/savecategorytree") ?>" id="mainform" method="post">
    <?php foreach ($categories[0] as $pCategory) { ?>
        <div class="hidden detailCategory" id="detail<?= $pCategory['farpost_category_id'] ?>">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Ссылка</th>
                    <th>Соответствие</th>
                </tr>
                <?php
                if (!empty($categories[$pCategory['farpost_category_id']]))
                    foreach ($categories[$pCategory['farpost_category_id']] as $category) {
                        if (isset($relations[$category['farpost_category_id']]))
                            $rel = $relations[$category['farpost_category_id']]["category_id"];
                        else
                            $rel = 0;
                        ?>
                        <tr>
                            <td>
                                <?= $category['farpost_category_id'] ?>
                            </td>
                            <td>
                                <?= $category['name'] ?>
                            </td>
                            <td>
                                <a href="http://farpost.ru/<?= $category['link'] ?>" target="blank">
                                    farpost.ru/<?= $category['link'] ?>
                                </a>
                            </td>
                            <td>
                                <select data-id="<?= $category['farpost_category_id'] ?>" name="depend[<?= $category['farpost_category_id'] ?>]" class="depending">
                                    <option value="0" <?= ($rel == 0) ? 'selected="selected"' : '' ?>>(не указано)</option>
                                    <?php foreach ($sourceCategories[0] as $spCategory) { ?>
                                        <optgroup label="<?= $spCategory['name'] ?>">
                                            <?php
                                            if (!empty($sourceCategories[$spCategory['category_id']]))
                                                foreach ($sourceCategories[$spCategory['category_id']] as $sCategory) {
                                                    ?>
                                                    <option value="<?= $sCategory['category_id'] ?>" <?= ($rel == $sCategory['category_id']) ? 'selected="selected"' : '' ?>>
                                                        <?= $sCategory['name'] ?>
                                                    </option>
                                                <?php } ?>
                                        </optgroup>

                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                    <?php }
                ?>
            </table>
        </div>
        <?php
    }
    ?>
    <input type="submit" value="Сохранить изменения">
</form>
<script>
    function changeCategory(){
        $("div.detailCategory").hide();
        $("#detail" + $("#category").val()).show();
    }
    changeCategory();
    $("#category").change(changeCategory);
    
    var changes = {};
    $("select.depending").change(function() {
        changes[$(this).data("id")] = true;
    })
    $("#mainform").submit(function() {
        $("input[name=changes]").remove();
        for (var key in changes) {
            $("<input>", {type: "hidden", value: key, name: "changes[]"}).appendTo("#mainform");
        }
    })
</script>