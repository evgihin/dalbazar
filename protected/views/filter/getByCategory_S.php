<?php /* @var $this FilterController */ ?>
<?php /* @var $filter array */ ?>
<?php /* @var $params array */ ?>
<?php /* @var $value int */ ?>

<label for="<?= $fieldName ?>S<?= $filter['filter_id'] ?>"><?= $filter['name'] ?>:</label>

<select
  name="<?= $fieldName ?>[filterS][<?= $filter['filter_id'] ?>]"
  autocomplete="off"
  id="<?= $fieldName ?>S<?= $filter['filter_id'] ?>"
  data-id="<?= $filter['filter_id'] ?>"
  <?php if ($filter['depend']) { ?>
    data-depend="<?= $filter['depend'] ?>"
  <?php } ?>
  >
    <?php if (!$filter['depend']): ?>
    <option value="-1">(не указано)</option>
    <?php
    foreach ($params as $param) {
      ?>
      <option value="<?= $param['filter_param_id'] ?>" <?= ($value == $param['filter_param_id']) ? "selected" : "" ?>><?= $param['name'] ?></option>
      <?php
    }
  else:
    ?>
    <option value="-1">(пока недоступно)</option>
  <?php endif; ?>
</select>
