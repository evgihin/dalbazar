<?php
$this->beginContent('//layouts/main'); ?>
<table id="UPButtons">
  <tr>
    <td>
      <div>
        <a href="<?= $this->createUrl('panel/advert/index') ?>"><img src="images/theme/advert.png">Объявления</a>
      </div>
    </td>
    <td>
      <div>
        <a href="<?= $this->createUrl('panel/payment/index') ?>"><img src="images/theme/purse.png">Платежи</a>
      </div>
    </td>
    <td>
      <div>
        <a href="<?= $this->createUrl('panel/user/edit') ?>"><img src="images/theme/user.png">Учетная запись</a>
      </div>
    </td>
  </tr>
</table>
<?= $content; ?>
<?php
$this->endContent();