<?php /* @var $this SiteController */ ?>
<?php if (count($params) == 0) { ?>
  <option value="-1">(пусто)</option>
<?php } else { ?>
  <option value="-1">(не указано)</option>
  <?php
}
foreach ($params as $param) {
  ?>
  <option value="<?= $param['filter_param_id'] ?>" <?= ($value == $param['filter_param_id']) ? 'selected' : '' ?>><?= $param['name'] ?></option>
  <?php
}
