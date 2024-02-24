<?php /* @var $this AdvertController */ ?>
<table style="width: 100%">
    <tr>
        <th>#</th>
        <th>Имя</th>
        <th># категории</th>
        <th>действия</th>
    </tr>
    <?php
    foreach ($adverts as $advert) {
        $id = $advert['advert_id'];
        ?>
    <tr>
        <td><?= $advert['advert_id'] ?></td>
        <td><?= $advert['zagolovok'] ?></td>
        <td><?= $advert['category_id'] ?></td>
        <td><a href="<?= $this->createUrl('admin/advert/unpublic',array('advert_id'=>$id)) ?>" title="Убрать с публикации"><img src="images/theme/edit.png"></a></td>
    </tr>
        <?php
    }
    ?>
</table>