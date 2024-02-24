<?php
/** @var $this Controller */
/** @var $filter array Описание фильтра, его параметры */
/** @var $params array Параметры фильтра в виде списка */
/** @var $checkDepend bool Проверять, зависимый фильтр или нет. По умолчанию TRUE */
/** @var $checked array Необязательный массив,
 * если элемент под ИД-ом $id присутствует в массиве значит он будет отмечен */
/** @var $showExtBtn принудительно показать либо спрятать кнопку "все параметры".
 * Если не установлено - решать автоматически на основе данных формы */
if (!isset($checkDepend))
	$checkDepend = true;
if (!isset($checked))
	$checked = array();


$j = 0;
if ($checkDepend && $filter['depend']) {
	?>
	<div class="filterParam">(пока недоступно)</div>
	<?php
} else {
	foreach ($params as $param) {
            
		$filterName = 'filters[' . $param['filter_param_id'] . ']';
		?>
		<div class="filterParam">
			<input
		        type="checkbox"
		        id="params<?= $param['filter_param_id'] ?>"
		        name="<?= $filterName ?>"
				<?php if (in_array($param['filter_param_id'], $checked)) echo 'checked'; ?>
		        >
			<label for="params<?= $param['filter_param_id'] ?>"><?= $param['name']; ?></label>
		</div>
		<?php
		$j++;
		//тормозим коней если показывается слишком много категорий
		if ($j >= $filter['top_count'])
			break;
             
             

	}
	//решаем показывать или нет кнопку "все параметры"
	if ((isset($showExtBtn) && $showExtBtn) || (!isset($showExtBtn) && $j >= $filter['top_count'])) {
		?>
					<a href="<?= $this->createUrl('category/allParams', array('filter_id' => $filter['filter_id'])) ?>" class="allParams" title="показать остальные параметры">все параметры</a>
		<?php
	}
}