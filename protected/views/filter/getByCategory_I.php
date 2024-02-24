<?php /* @var $this FilterController */ ?>
<?php /* @var $filter array */ ?>
<?php /* @var $value int */ ?>

<label for="<?= $fieldName ?>I<?= $filter['filter_id'] ?>"><?= $filter['name'] ?>:</label>
<input
  type="text"
  name="<?= $fieldName ?>[filterI][<?= $filter['filter_id'] ?>]"
  id="<?= $fieldName ?>I<?= $filter['filter_id'] ?>"
  autocomplete="off"
  size="<?= mb_strlen((string) $filter['to']) ?>"
  class="digits slided"
  min="<?= $filter['from'] ?>"
  max="<?= $filter['to'] ?>"
  step="<?= $filter['step'] ?>"
  value="<?= ($value)?$value:$filter['from'] ?>"
  >&nbsp;<?= $filter['piece'] ?>
<div id="slider<?= $filter['filter_id'] ?>" class="advAddSlider"></div>
<input type="checkbox" class="advUnknown" id="unknown<?= $filter['filter_id'] ?>" <?= ($value===false)?"checked":"" ?> ><label for="unknown<?= $filter['filter_id'] ?>">неизвестно</label>