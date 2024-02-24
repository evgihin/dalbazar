<?php
/* @var $this FarpostController */
?>
<h1>Создание задания парсинга FarPost</h1>
<form action="<?= $this->createUrl("admin/farpost/saveparsetask") ?>" method="post">
    Категория: 
    <select id="category">
        <option value="0">(не указано)</option>
        <?php foreach ($categories[0] as $pCategory) { ?>
            <option value="<?= $pCategory['farpost_category_id'] ?>"><?= $pCategory['name'] ?></option>
        <?php } ?>
    </select><br>
    Парсить первые <input type="text" size="5" value="10"> страниц<br>
    С датой добавления не более <select>
        <option value="1">1 день</option>
        <option value="2">2 дня</option>
        <option value="3">3 дня</option>
        <option value="4">4 дня</option>
        <option value="5">5 дней</option>
        <option value="6">6 дней</option>
        <option value="7">7 дней</option>
        <option value="10">10 дней</option>
        <option value="15">15 дней</option>
    </select>
    <br>
    <input type="submit" value="Добавить">
</form>