<?php
/**
 * @var $logins array
 * @var $pages CPagination
 */
$this->breadcrumbs = array(
    'Пользователи'=>'admin/user',
    'Стоп-слова'=>'admin/user/listStopLogin'
)
?>

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
<form id="logins" action="<?= $this->createUrl('admin/user/stopLogin') ?>" method="post">
    <table>
        <th></th>
        <th>Логин</th>
        <th>действие</th>
        <?php
        foreach ($logins as $login) {
            ?>
            <tr>
                <td><input type="checkbox" name="items[<?= $login['stop_login_id'] ?>]"></td>
                <td><?= $login['login'] ?></td>
                <td>
                    <a href="<?= $this->createUrl("admin/user/removeStopLogin", array('id' => $login['stop_login_id'])) ?>" title="Удалить слово">
                        <img src="/images/theme/icon-20-trash.png">
                    </a>
                </td>
            </tr>
        <?php }
        ?>
    </table>
</form>

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