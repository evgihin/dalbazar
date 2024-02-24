<?php
/* @var $this FarpostController */
/* @var $adverts array список объявлений */
/* @var $count int Количество неразобранных объявлений */
/* @var $pages CPagination Странички */
/* @var $farpostCategoryId int ид выбранной категории */
/* @var $farpostCategories array список категорий */
$this->act->extendLink("deleteAll", array("admin/farpost/cleanParsed"), "очистить список", "images/theme/icon-32-cancel.png");

$this->breadcrumbs = array(
    "Парсинг"=>array("admin/parser"),
    "Отпарсенные объявления" => array("admin/farpost/listParseAdvert")
    );
?>
<h1>Список полученных с farpost объявлений (<?= $count ?>)</h1>
<?php
echo CHtml::dropDownList("farpost_cateogry_lpa", $farpostCategoryId, array(0=>"(выберите категорию)")+$farpostCategories)."<br>";
if (empty($adverts)):
    ?>
Нет отпарсенных объявлений. Для добавления <?= CHtml::link("создайте задачу парсинга",array('admin/farpost/addParseTask')) ?>
<?php else: ?>
<table>
    <tr>
        <th>Фото</th>
        <th>Заголовок</th>
        <th>Дата</th>
    </tr>
    <?php
    foreach ($adverts as $data) {
        $advert = unserialize($data['data']);
        $link = $this->createUrl('admin/farpost/editParseAdvert', array("farpost_category_id" => $farpostCategoryId, "farpost_parsed_id" => $data['farpost_parsed_id']));
        ?>
        <tr>
            <td>
                <a href="<?= $link ?>">
                    <?php
                    if (!empty($advert['images']))
                        echo CHtml::image($advert['images'][0], $advert['title'], array('width' => "120"))
                        ?>
                </a>
            </td>
            <td>
                <a href="<?= $link ?>">
                    <?= $advert['title'] ?>
                </a>
            </td>
            <td>
                <?= date("d.m.Y H:i:s", $data['time']) ?>
            </td>
        </tr>
        <?php
    }
    ?>
</table>
<?php $this->widget('CLinkPager', array(
    'pages' => $pages,
)) ?>
<?php endif; ?>
<script>
    $("#farpost_cateogry_lpa").change(function(){
        document.location.href = "<?= $this->createUrl("admin/farpost/listParseAdvert",array("farpost_category_id"=>"-id-")) ?>".replace("-id-", $(this).val());
    })
</script>