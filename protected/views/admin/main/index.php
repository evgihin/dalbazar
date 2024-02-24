<h1>Добро пожаловать в панель управления сайтом</h1>
<div class="mainPageBlock">
    <a href="<?= $this->createUrl("admin/filter/list") ?>">
        <div class="mainPageItem cornered5 bordered">
            <img class="mainPageIcon" src="/images/theme/Filter-List-icon.png">
            Фильтры
        </div>
    </a>
    <a href="<?= $this->createUrl("admin/category/list") ?>">
        <div class="mainPageItem cornered5 bordered">
            <img class="mainPageIcon" src="/images/theme/folder.png">
            Категории
        </div>
    </a>
    <a href="<?= $this->createUrl("admin/subcategory/list") ?>">
        <div class="mainPageItem cornered5 bordered">
            <img class="mainPageIcon" src="/images/theme/folder2.png">
            Подкатегории
        </div>
    </a>
    <a href="<?= $this->createUrl("admin/advert") ?>">
        <div class="mainPageItem cornered5 bordered">
            <img class="mainPageIcon" src="/images/theme/sticker.jpg">
            Объявления
        </div>
    </a>
    <a href="<?= $this->createUrl("admin/email/delivery") ?>">
        <div class="mainPageItem cornered5 bordered">
            <img class="mainPageIcon" src="/images/theme/iphone_mail_icon.png">
            E-mail
        </div>
    </a>
    <a href="<?= $this->createUrl("admin/parser") ?>">
        <div class="mainPageItem cornered5 bordered">
            <img class="mainPageIcon" src="/images/theme/spider-icon.png">
            Парсинг
        </div>
    </a>
</div>
<div class="clear"></div>
<?php if ($gitLog) { ?>
    <h1>Последние 10 обновлений сайта:</h1>
    <a href="<?= $this->createUrl("admin/log/git") ?>">Посмотреть все обновления</a><br>
    <?php foreach ($gitLog as $commit) { ?>
        <hr>
        <?= $commit['author'] . ". " . date('d.m.Y h:m', strtotime($commit['date'])) ?><br>
        <?= nl2br($commit['message']) ?>
        <?php
    }
}
?>