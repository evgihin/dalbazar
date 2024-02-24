<?php
/* @var $this Controller */
/* @var $filter array Описание фильтра, его параметры */


$filterNameFrom = 'filteri[' . $filter['filter_id'] . '][from]';
$filterNameTo = 'filteri[' . $filter['filter_id'] . '][to]';
$filterNameMin = 'filteri[' . $filter['filter_id'] . '][min]';
$filterNameMax = 'filteri[' . $filter['filter_id'] . '][max]';
?>
от&nbsp;<input
  type="text"
  class="intSliderFrom"
  id="paramFrom<?= $filter['filter_id'] ?>"
  name="<?= $filterNameFrom ?>"
  size="<?= (($len = strlen($filter['to']) - 1) > 1) ? $len : '1' ?>"
  value="<?= $filter['from'] ?>"><?= $filter['piece'] ?>&nbsp;-&nbsp;
до&nbsp;<input
  type="text"
  class="intSliderTo"
  id="paramTo<?= $filter['filter_id'] ?>"
  name="<?= $filterNameTo ?>"
  size="<?= (($len = strlen($filter['to']) - 1) > 1) ? $len : '1' ?>"
  value="<?= $filter['to'] ?>"><?= $filter['piece'] ?>
<div class="intSlider" id="slider<?= $filter['filter_id'] ?>"></div>
<input
  type="hidden"
  class="intSliderStep"
  id="paramStep<?= $filter['filter_id'] ?>"
  name="step[]"
  value="<?= $filter['step'] ?>">
<input
  type="hidden"
  class="intSliderMin"
  name="<?= $filterNameMin ?>"
  value="<?= $filter['from'] ?>">
<input
  type="hidden"
  class="intSliderMax"
  name="<?= $filterNameMax ?>"
  value="<?= $filter['to'] ?>">