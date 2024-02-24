<?php
/* @var $this CController */
$linkParams = array();
if ($email->to)
    $linkParams = array("email" => $email->to, "code" => md5($email->to . "fuckingmailsecretword!!!..."));
?>
<html>
    <meta charset="UTF-8" />
    <body>
        <a href="http://www.dalbazar.ru/">
            <img width="219" height="69" src="http://www.dalbazar.ru/images/theme/logo2.png">
        </a>
        <br>
        <?= $content ?>
        <br>
----------------------------------------------<br>
С уважением,<br>
команда ресурса <a href="www.dalbazar.ru">Дальбазар.ру (www.dalbazar.ru)</a><br>
<br>
тел. 8 (4232) 93 99 99<br>
e-mail: <a href="mailto:939999@mail.ru">939999@mail.ru</a><br>
<br>
<br>
        <a href="<?= $this->createAbsoluteUrl("email/unsubscribe", $linkParams) ?>">Отписаться от рассылки</a>
    </body>
</html>
