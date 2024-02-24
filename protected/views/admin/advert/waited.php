<?php 
/* @var $this AdvertController */ 
/* @var $pages CPagination */ 
/* @var $items array */ 

?>
<table style="width: 100%">
    <tr>
        <th>#</th>
        <th>Имя</th>
        <th># категории</th>
        <th>действия</th>
    </tr>
    <?php
    foreach ($items as $advert) {
        $id = $advert['advert_id'];
        ?>
    <tr>
        <td><?= $advert['advert_id'] ?></td>
        <td><?= $advert['zagolovok'] ?></td>
        <td><?= $advert['category_id'] ?></td>
        <td>
            <a href="<?= $this->createUrl('admin/advert/accept',array('advert_id'=>$id)) ?>" title="Опубликовать на сайте">
                <img src="images/theme/check.png">
            </a>
            <a href="<?= $this->createUrl('admin/advert/reject',array('advert_id'=>$id)) ?>" title="Отклонить объявление">
                <img src="images/theme/cancel.png">
            </a>
        </td>
    </tr>
        <?php
    }
    ?>
</table>

<?php $this->widget('CLinkPager', array(
    'pages' => $pages,
)) ?>