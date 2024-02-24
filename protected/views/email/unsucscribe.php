<?php
//страница с выбором, от чего отписаться
/* @var $this TestController */
/* @var $user array */
?>
<h1>Отписаться от рассылки на ящик <b><?= $user['email'] ?></b></h1>
<form action="<?= $this->createUrl("email/CompleteUnsubscribe", array("email"=>$user['email'],"code" => $code)) ?>" method="post">
    <input type="hidden" name="email" value="<?= $user['email'] ?>">
    Отписаться от:<br><br>
    <?php
    if ($user['recieve_email_news']) {
        ?>
        <input type="checkbox" name="unsucscribe[]" value="news"> Новости<br>
        <?php
    }
    if ($user['recieve_email_notifications']) {
        ?>
        <input type="checkbox" name="unsucscribe[]" value="notifications"> Уведомления<br>
    <?php } ?>
    <br>
    <input type="submit" value="Отписаться">
</form>