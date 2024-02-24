<?php

/* @var $phone array удаляемый телефон */
?>
Вы действительно хотите удалить телефон <b><?= $phone['phone'] ?></b>?<br>
<a class="button" href="<?=$this->createUrl('panel/user/deletePhone',array('phoneId'=>$phone['phone_id'],'confirmed'=>1))?>">Да</a>
<a class="button" href="<?= $this->createUrl('panel/user/edit') ?>">Нет</a>

