<?php
/* @var $this AdvertController */
/* @var $rootCategories array Корневые категории, в них нельзя добавлять объявления, только для классификации */
/* @var $shortSubCategories array Подкатегории, ключем массива является ИД родителя, включены только ид, ид родителя и имя */
?>
<tr>
    <td><label for = "category">Категория:</label></td>
    <td colspan="2">
        <select name = "add[rootcategory]" id = "category" class="advCategory" autocomplete = "off" size = "8">
            <option value = "0" selected = "">Выберите категорию</option>
            <?php foreach ($rootCategories as $category) {
                ?>
                <option value="<?= $category['category_id'] ?>"><?= $category['name'] ?></option>
                <?php
            }
            ?>
        </select>
        <select name="add[category]" id="subcategory" class="advSubCategory" autocomplete="off" size="8">
            <option value="0" selected="">Выберите подкатегорию</option>
        </select>
    </td>
</tr>
<script>
    function refreshSubCategory(val) {
        var data = <?= CJSON::encode($shortSubCategories) ?>;
        if (val == "0")
            $("#subcategory").hide();
        else {
            obj = $("#subcategory").empty();
            $("<option>").text("Выберите подкатегорию").attr("value", "0").attr("selected", "selected").appendTo(obj);
            for (i in data[val]) {
                $("<option>").text(data[val][i].name).attr("value", data[val][i].id).appendTo(obj);
            }
            obj.show();
        }
    }
    refreshSubCategory($("#category").val());

    $("#category").change(function() {
        refreshSubCategory($(this).val());
    });
</script>