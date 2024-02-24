<?php
/* @var $this PanelController */
/* @var $adverts array */
?>
<h1>Мои объявления</h1>
<table class="data" style="width: 100%">
    <tr>
        <th>id</th>
        <th></th>
        <th>Название</th>
        <th>Текст</th>
        <th>Создано</th>
        <th>Завершено</th>
        <th>Статус</th>
        <th>Действие</th>
    </tr>
    <?php
    foreach ($adverts as $advert) {
        ?>
        <tr class="<?= Helpers::odder() ?>">
            <td><?= $advert['advert_id'] ?></td>
            <td><a href="<?= $this->createUrl('advert/show', array('advert_id' => $advert['advert_id'])) ?>"><img src="<?= Helpers::getImageUrl($advert['image'], 120) ?>"></a></td>
            <td><?= $advert['zagolovok'] ?></td>
            <td><?= Helpers::cutString($advert['text'], 100) ?></td>
            <td><?= date('d.m.Y h:i', $advert['create_time']) ?></td>
            <td><?= date('d.m.Y h:i', $advert['expirate_time']) ?></td>
            <td><?= $advert['state_name'] ?></td>
            <td>
                <?php if ($advert['state'] == 'expired') { ?>
                    <img src="images/theme/edit_disabled.png" title="Редактировать истекшие объявления нельзя">
                <?php } else { ?>
                    <a href="<?= $this->createUrl('panel/advert/edit', array('advert_id' => $advert['advert_id'])) ?>" title="Редактировать объявление"><img src="images/theme/edit.png"></a>
                <?php } ?>
                <a href="<?= $this->createUrl('panel/advert/delete', array('advert_id' => $advert['advert_id'])) ?>" title="удалить">
                    <img src="images/theme/icon-20-trash.png">
                </a>
            </td>
        </tr>
        <?php
    }
    ?>
</table>