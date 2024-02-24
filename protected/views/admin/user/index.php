<?php
/**
 * @var $pages CPagination
 * @var $users array
 * @var $phones array
 * @var $count int
 */
$this->breadcrumbs = array(
    'Пользователи' => 'admin/user',
        );
$this->act->add("admin/user/add");
?>
<a href="<?= $this->createUrl("admin/user/liststoplogin") ?>">Управление стоп-словами</a>
<h3>Пользователи сайта:</h3>
Всего: <?= $count ?>
<table width="100%">
    <tr>
        <th>ИД</th>
        <th>логин</th>
        <th>ФИО</th>
        <th>email</th>
        <th>город</th>
        <th>админ?</th>
        <th>телефоны</th>
    </tr>
    <?php
    foreach ($users as $user) {
        if ($user['user_id'] != 1):
            ?>
            <tr>
                <td><?= CHtml::link($user['user_id'], array("admin/user/edit","user_id"=>$user['user_id'])) ?></td>
                <td><?= CHtml::link($user['login'], array("admin/user/edit","user_id"=>$user['user_id'])) ?></td>
                <td><?= $user['lastname'] . ' ' . $user['name'] . ' ' . $user['middlename'] ?></td>
                <td><?= $user['email'] ?></td>
                <td><?= $user['city_id'] ?></td>
                <td><?= $user['admin_level'] ? "да" : "нет" ?></td>
                <td><?php
                    if (isset($phones[$user['user_id']]))
                        foreach ($phones[$user['user_id']] as $phone) {
                            //var_dump($phone);
                            echo $phone['phone'] . '<br>';
                        }
                    ?></td>
            </tr>
            <?php
        endif;
    }
    ?>
</table>
<div id="catPagination">
    <?php
    $this->widget('CLinkPager', array(
        'pages' => $pages,
        'prevPageLabel' => '←',
        'nextPageLabel' => '→',
        'cssFile' => FALSE,
        'header' => '',
        'footer' => '',
        'htmlOptions' => array('class' => '')
    ));
    ?>
</div>