<?php
/* Выдает пользователю страницу с расширенным фильтром S */
/* @var $this Controller */
/* @var $filter array Описание фильтра, его параметры */
/* @var $params array Параметры фильтра */


foreach ($params as $param) {
  $filterName = 'filters[' . $param['filter_param_id'] . ']';
  ?>
  <div class="filterParam">
    <input type="checkbox" id="params<?= $param['filter_param_id'] ?>" name="<?= $filterName ?>" checked >
    <label for="params<?= $param['filter_param_id'] ?>"><?= $param['name']; ?></label>
  </div>
  <?php
}
?>
<a href="<?= $this->createUrl('category/allParams', array('filter_id' => $filter['filter_id'])) ?>" class="allParams" title="показать остальные параметры">все параметры</a>