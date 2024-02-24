<?php
/* @var $this FarpostController */
/* @var $oldAdvert array Нераспознанное объявление */
/* @var $newAdvert array Распознанное объявление */
/* @var $farpostCategory array Категория */
/* @var $farpostSubCategory array Подкатегория */
/* @var $id int ИД текущего объявления */
/* @var $nextId int ИД следующего объявления в списке */
/* @var $nextCategoryId int ИД категории, с которой работают */
/* @var $cities array Список городов */
/* @var $filters array Список фильтров */
/* @var $params array Список параметров фильтров */
/* @var $filterRelations array Связи категории и фильтров */

$this->act->delete("parse");
$this->act->close("admin/farpost/listParseAdvert");
$this->act->save("parse");
if ($nextId)
    $this->act->extendLink("next", array('admin/farpost/editParseAdvert', "farpost_parsed_id" => $nextId, "farpost_category_id"=>$nextCategoryId), "дальше", "images/theme/arrow_right.png");
$this->breadcrumbs = array(
    "Парсинг" => array("admin/parser"),
    "Отпарсенные объявления" => array("admin/farpost/listParseAdvert"),
    "Объявление №" . $id => array("admin/farpost/editParseAdvert", "farpost_parsed_id" => $id)
        )
?>
<?php if ($this->model && $this->model->hasErrors()): ?>
<div class="red"><?php echo Helpers::errorsToText($this->model->getErrors()) ?></div>
<?php endif; ?>
<h1>Редактирование Farpost объявления</h1>
<form id="parse" action="<?= $this->createUrl('admin/farpost/saveParseAdvert', array('farpost_parsed_id' => $id, 'next' => $nextId, "farpost_category_id"=>$nextCategoryId)) ?>" method="post">
    <table>
        <tr>
            <th></th>
            <th>Исходное</th>
            <th>Конечное</th>
        </tr>
        <tr>
            <td>Заголовок</td>
            <td><?= $oldAdvert['title'] ?></td>
            <td><input type="text" size="35" value="<?= $newAdvert['title'] ?>" name="zagolovok"></td>
        </tr>
        <tr>
            <td>Добавлено</td>
            <td><?= strip_tags($oldAdvert['create_time']) ?></td>
            <td><?= date('H:i d.m.Y', $newAdvert['create_time']) ?></td>
        </tr>
        <tr>
            <td>Актуально</td>
            <td><?= strip_tags($oldAdvert['expirate_time']) ?></td>
            <td><?= date('H:i d.m.Y', $newAdvert['expirate_time']) ?></td>
        </tr>
        <tr>
            <td>Контакты</td>
            <td><?= $oldAdvert['contacts'] ?></td>
            <td><textarea rows="5" cols="35" name="contacts"><?= $newAdvert['contacts'] ?></textarea></td>
        </tr>
        <tr>
            <td>Город</td>
            <td><?= $oldAdvert['city'] ?></td>
            <td>
                <?php 
                if (!$newAdvert['city']['city_id'])
                    $class = array("class"=>"red");
                else
                    $class = array();
                echo CHtml::dropDownList("city", $newAdvert['city']['city_id'], array(0 => "(не указано)") + Helpers::simplify($cities, "city_id", "name"), $class);
                        ?>
            </td>
        </tr>
        <tr>
            <td>Категория</td>
            <td><?php
                echo $farpostCategory['name'];
                if ($farpostSubCategory)
                    echo ' (' . $farpostSubCategory['name'] . ')';
                ?></td>
            <td><?php
                $this->widget("application.widget.UCategoryChanger", array(
                    'category_id' => $newAdvert['category_id'],
                    'name' => 'category'
                ))
                ?></td>
        </tr>
        <tr>
            <td>Текст</td>
            <td><div style="height: 170px; overflow: auto;" class="bordered"><?= nl2br($oldAdvert['text']) ?></div></td>
            <td><textarea rows="10" cols="35" name="text"><?= $newAdvert['text'] ?></textarea></td>
        </tr>
        <tr>
            <td>Цена</td>
            <td><?= $oldAdvert['price'] ?></td>
            <td><input  type="text" value="<?= $newAdvert['price'] ?>" name="price">р.</td>
        </tr>
        <tr>
            <td>Параметры</td>
            <td>
                <?php
                foreach ($oldAdvert['params'] as $id => $val) {
                    echo $id . ": <b>" . $val . "</b><br>";
                }
                ?>
            </td>
            <td id="filters"><?php
                foreach ($filterRelations as $categoryId => &$filterList) {
                    ?>
                    <div id="filterGroup<?= $categoryId ?>" class="hidden filters">
                        <?php
                        
                        $paramVals = array();
                        foreach ($filterList as $filterId) {
                            $filter = &$filters[$filterId];
                            if (!empty($newAdvert['params'][$filterId])){
                                echo $filter['name'], " : ";
                                $paramVals[$filterId] = $newAdvert['params'][$filterId];
                            } else {
                                echo '<span class="red">' . $filter['name'], "</span> : ";
                                $paramVals[$filterId] = 0;
                            }

                            switch ($filter['type']) {
                                case "s":
                                    if (isset($params[$filterId])) {
                                        if ($filter['depend'])
                                            $params[$filterId] = array(0 => "(пока недоступно)");
                                        else {
                                            $params[$filterId] = array(0 => "(не указано)") + $params[$filterId];
                                        }
                                        echo CHtml::dropDownList("filter[$filterId]", $paramVals[$filterId], $params[$filterId], array("depend" => $filter['depend'], "autocomplete" => "off"));
                                    }
                                    break;
                                case "i":
                                    ?>
                                    <input type="text" name="filter[<?= $filterId ?>]">
                                    <?php
                                    break;
                            }
                            ?>
                            <br>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
            </td>
        </tr>
        <tr>
            <td>Ссылка</td>
            <td colspan="2"><?= CHtml::link($newAdvert['source_link'], $newAdvert['source_link'], array("target" => "blank")) ?></td>
        </tr>
        <tr>
            <td>Фото</td>
            <td colspan="2">
                <?php
                if ($oldAdvert['images'])
                    foreach ($oldAdvert['images'] as $image) {
                        echo CHtml::image($image, '', array("width" => "100"));
                    }
                ?>
            </td>
        </tr>
    </table>
</form>

<script>
    function changeCategory() {
        $(".filters").hide();
        $("#filterGroup" + $("#category").val()).show();
    }
    changeCategory();
    $("#category").change(changeCategory);

    var filterVals = <?= CJSON::encode($newAdvert['params']) ?>;
    //получение параметров зависимых фильтров
    function findDependentAndReplace(id, val) {
        val = parseInt(val);
        if (val != 0) {
            $("#filters select:visible").each(function() {

                if ($(this).attr("depend") == id) {
                    var elem = $(this);
                    var elId = /\d+/.exec(elem.attr("id"))[0];
                    $.get("<?= $this->createUrl("filter/getDependentParams") ?>", {"filter": elId, "value": val, "selected": filterVals[elId]}, function(d) {
                        elem.replaceWith($(d));
                    });
                }
            });
        }
    }


    $("#filters select").change(function() { //вешаем следилку, которая будет проверять зависимость параметров при изменении любого из них
        var id = /\d+/.exec($(this).attr("id"))[0];
        findDependentAndReplace(id, $(this).val());
    }
    );
    $("#filters select:visible").each(function() { //вешаем следилку, которая будет проверять зависимость параметров при изменении любого из них
        var id = /\d+/.exec($(this).attr("id"))[0];
        findDependentAndReplace(id, $(this).val());
    });
    $("#parse").submit(function(){
        $(".filters:hidden").remove();
    })
</script>