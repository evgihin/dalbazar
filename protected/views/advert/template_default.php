<?php
/* @var $this Controller */
/* @var $advert array */
/* @var $images array */
/* @var $params array */
/* @var $phones array */
/* @var $advert_id int */
/* @var $parentCategory array инфа о родительской категории */
Yii::app()->clientScript->registerCssFile('/css/template_default.css');
Yii::app()->clientScript->registerScriptFile('/js/lightbox.js');
Yii::app()->clientScript->registerCssFile('/css/lightbox.css');
$this->breadcrumbs = array();
if ($parentCategory)
    $this->breadcrumbs[$parentCategory['name']] = array('category/show', 'alias1' => (int) $parentCategory['category_id']);
$this->breadcrumbs[$advert['category']] = array('category/show', 'alias1' => (int) $advert['category_id']);
$this->breadcrumbs[] = Helpers::cutString($advert['zagolovok'], 50);

$this->articleRight = '<span class="aCreation" title="дата создания">' . date('d.m.Y h:m', $advert['create_time']) . '</span>';
?>
<div id="mypopup">
    <div id="mp_close" title="Закрыть окно"></div>
    <div id="mp_text">
        Для поднятия объявления отправьте СМС с кодом <b>regbazar <?= $advert_id ?></b> на номер <b>4345</b>
        <!--        <div id="mp_loader"></div>-->
    </div>
</div>
<script>
    $('#mp_close').click(function() {
        $('#mypopup').hide()
    });
</script>
<div id="aFotoBlock">
    <?php foreach ($images as $image) { ?>
    <a href="<?= Helpers::getImageUrl($image) ?>" rel="lightbox[all]"><img src="<?= Helpers::getImageUrl($image, 120, 120) ?>" width="120px"></a>
    <?php }
    ?>
</div>
<div id="aDescription">
    <h1><?= $advert['zagolovok'] ?></h1>
    <div id="aParams">
        <?php foreach ($filters as $filter) { ?>
            <span class="aParamName"><?= $filter['filter_name'] ?>:</span> <b><?= ($filter['type']=="s")?$filter['filter_param_name']:$filter['value'].$filter['piece'] ?></b><br>
        <?php } ?>
    </div>
    <div id="aFullText">
        <h2>Описание:</h2>
        <?= nl2br( $advert['text'] ) ?>
    </div>

    <h1>Контактные данные:</h1>
    <div id="aPhones">
        <?php
        $tempPhones = array();
        foreach ($phones as $phone) {
            if ($phone['temp'])
                $tempPhones[] = $phone;
            else
                echo $phone['phone'] . "<br>";
        }
        ?>
    </div>
    <?php if (!empty($tempPhones)) {
        ?>
        <div id="aPhonesSpoiler" class="additional">
            <a class="inactive">
                Показать дополнительные телефоны
            </a>
            <div id="aHidden" class="hidden aTempPhones">
                <?php
                foreach ($tempPhones as $phone) {
                    echo $phone['phone'] . "<br>";
                }
                ?>
            </div>
        </div>
        <script>
            $("#aPhonesSpoiler").click(function() {
                $("#aHidden").toggle(300);
            })
        </script>
        <?php
    }
    ?>

    <h1>Дополнительно:</h1>
    <a class="aUp inactive">Поднять объявление (≈ 5р.)</a><br>
    <div class="hidden additional" id="aUpInstructions">
        Для поднятия объявления отправьте СМС с текстом 
        <b>regbazar <?= $advert_id ?></b> на номер <b>4345</b>.<br>
        Стоимость СМС-сообщения составляет примерно <span class="red bold">5 рублей</span>. 
        Точную стоимость можно узнать <a href="http://smsrent.ru/tariffs/RU/4345/" target="_blank">тут</a>.
    </div>

    <a class="aBold inactive">Сделать жирным и поднять (≈ 9р.)</a><br>
    <div class="hidden additional" id="aBoldInstructions">
        Для выделения жирным объявления отправьте СМС с текстом
        <b>regbazar <?= $advert_id ?></b> на номер <b>2320</b>.<br>
        При выделении Ваше сообщение автоматически поднимется вверх в выдачи.<br>
        Стоимость СМС-сообщения составляет примерно <span class="red bold">9 рублей</span>. 
        Точную стоимость можно узнать <a href="http://smsrent.ru/tariffs/RU/2320/" target="_blank">тут</a>.
    </div>

    <a class="aGold inactive">Опубликовать на главной странице на сутки (≈ 150р.)</a><br>
    <div class="hidden additional" id="aGoldInstructions">
        Для публикации объявления на главной странице сайта отправьте СМС с текстом 
        <b>regbazar <?= $advert_id ?></b> на номер <b>8385</b>.<br>
        При выделении Ваше сообщение автоматически поднимется вверх в выдачи, 
        прикрепится в рекламном блоке категории .<br>
        Стоимость СМС-сообщения составляет примерно <span class="red bold">150 рублей</span>. 
        Точную стоимость можно узнать <a href="http://smsrent.ru/tariffs/RU/2320/" target="_blank">тут</a>.
    </div>
    <?php if ($advert['provider']!="site"): ?>
    <br><a href="<?= $advert['link'] ?>" target="blank">Взято с альтернативного источника</a>
    <?php endif; ?>

</div>
<div class="clear"></div>
<script>
    var id =<?= $advert_id ?>;
    $('.aUp').click(function() {
        $('#aUpInstructions').toggle(300);
        return false;
    });
    $('.aBold').click(function() {
        $('#aBoldInstructions').toggle(300);
        return false;
    });
    $('.aGold').click(function() {
        $('#aGoldInstructions').toggle(300);
        return false;
    });
</script>
