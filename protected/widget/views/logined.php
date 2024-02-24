<?php
/* @var $this CWidget */
$app = Yii::app();
?>

<div id="userpanel">
  <div id="userinfo"><?php
if ($app->user->getState('lastname') || $app->user->getState('name') || $app->user->getState('middlename'))
  echo implode(' ', array($app->user->getState('lastname'), $app->user->getState('name'), $app->user->getState('middlename')));
else
  echo CHtml::link('укажите свое имя и фамилию', array('panel/user/edit'));
?></div>
  <a id="logout" href="<?= $this->controller->createUrl('login/logout') ?>">выход</a>
  <div id="userstatistic">
    новых сообщений: 0<br>
    ждут модерации: <?= $waitCount ?><br>
    опубликованных объявлений: <?= $publishedCount ?>
  </div>
  <a href="<?= $this->controller->createUrl('/panel') ?>" id="topanel">личный кабинет</a>
  <?php if ($app->user->getState('admin_level')): ?>
    <br>
    <a id="toadmin" href="<?= $this->controller->createUrl('/admin') ?>">управление сайтом</a>
  <?php endif; ?>
</div>