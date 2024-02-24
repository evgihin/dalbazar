<?php
/* @var $this SiteController */
/* @var $params array */
/* @var $filter array */

//$params = array_slice($params, 0, 5);
//определяем сколько категорий пустить в столбец
$size = count($params);
$columns = 1;
$countPerColumn = $size;
if ($size > 19) {
	$countPerColumn = ceil($size / 4);
	$columns = 4;
} elseif ($size > 14 && $size <= 19) {
	$countPerColumn = ceil($size / 3);
	$columns = 3;
} elseif ($size > 9 && $size <= 14) {
	$countPerColumn = ceil($size / 2);
	$columns = 2;
} else {
	$countPerColumn = $size;
	$columns = 1;
}
?>
<form id="allParams" method="post" action="<?= $this->createUrl('category/setAllParams') ?>">
    <input type="hidden" name="filterEId" value="<?= $id ?>">
    <table style="width: 100%">
		<tr>
			<?php
			$i = 0;
			foreach ($params as $param) {
				if ($columns == $i) {
					$i = 0;
					echo "</tr>\n<tr>";
				}
				$i++;
				?>
				<td>
					<input
						type="checkbox"
						name="filterE[<?= $param['filter_param_id'] ?>]"
						id="param<?= $param['filter_param_id'] ?>"
						<?= (in_array($param['filter_param_id'], $checked)) ? "checked" : "" ?>>
					<label for="param<?= $param['filter_param_id'] ?>"><?= $param['name'] ?></label>
				</td>

				<?php
			}
			echo str_repeat("<td></td>", $columns * $countPerColumn - $size);
			?>

		</tr>

    </table>
</form>