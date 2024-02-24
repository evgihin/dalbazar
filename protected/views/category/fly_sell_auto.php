<?php
/* @var $this SiteController */
/* @var $features Feature */
/* @var $pagination string html-код отображения страниц, и их отрисовка в браузере */
?>
<div id="advAttached" class="radiused">
    <?php
    // @todo перекинуть на свежесделанный виджет и подписи брать с базы данных (в виджете тоде сделать вывод из параметров$this->widget('')
    ?>
    <br>
	<?php
	$this->renderPartial('attached_adverts', array(
		'adverts' => (!empty($attached) ? $attached : array()),
		'values' => $attachedValues,
	));
	?>
</div>
<table id="advertList" class="data">
	<tr>
		<th>дата</th>
		<th></th>
		<th>модель</th>
		<th>год</th>
		<th colspan="2">двигатель</th>
		<th>пробег</th>
		<th>цена</th>
	</tr>
	<?php
	$oddMeter = false;
	foreach ($adverts as $advert) {
		$advertId = $advert['advert_id'];
		$values = (!empty($valuesAll[$advertId])) ? $valuesAll[$advertId] : array();
		?>
        <tr class="<?= ($oddMeter) ? 'even' : 'odd' ?><?= ($features->bold($advertId)) ? " ALBolded" : "" ?>" >
			<?php
                        
			if ($features->pictured($advertId) || $advertId==80) { //разукрашенные строки объявлений
				?>
				<td colspan="8" class="ALPictured" style="background-image:url(<?= Helpers::getImageUrl($images[$advert['advert_id']][0]['name'], 730) ?>); padding:0!important;">
					<a href="<?= $this->createUrl('advert/show', array('advert_id' => $advertId)) ?>">
						<div class="ALPicturedBlock">
							<div class="ALPicturedText cornered">
								<?= ($values[1]['value']) ? $values[1]['value'] . " " . $values[2]['value'] : $advert['zagolovok']; //фирма, модель ?><br>
								<?= (!empty($values[3]['value'])) ? $values[3]['value'] . '<br>' : ""; //год производства  ?>
								<?php
								$arr = array();
								if (isset($values[3]['value']))
									$arr[] = $values[3]['value'];
								if (isset($values[7]['value_short']))
									$arr[] = number_format($values[7]['value_short'] / 1000, 1) . '&nbsp;л.';
								if (isset($values[4]['value']))
									$arr[] = $values[4]['value'];
								if (isset($values[5]['value']))
									$arr[] = $values[5]['value'];
								if (isset($values[6]['value']))
									$arr[] = $values[6]['value'];
								echo implode(', ', $arr) . '<br>';
								?>
								<?= number_format($advert['price'], 0, '', '&nbsp;') . 'р.' ?>
							</div>
						</div>
					</a>
				</td>
			<?php } else { ?>
				<td><?= date('d.m.Y', $advert['create_time']) ?></td>
				<td class="advListPicture">
					<a href="<?= $this->createUrl('advert/show', array('advert_id' => $advertId)) ?>">
                                            <?php if (isset($images[$advert['advert_id']])): ?>
						<img src="<?= Helpers::getImageUrl($images[$advert['advert_id']][0]['name'], 120, 120) ?>">
                                                <?php else: ?>
                                                <img src="<?= Helpers::getImageUrl("", 120, 120) ?>">
                                                <?php endif;    ?>
					</a>
				</td>
				<td>
					<?= (!empty($values[1]['value']) && !empty($values[2]['value'])) ? $values[1]['value'] . " " . $values[2]['value'] : $advert['zagolovok']; //фирма, модель  ?>
				</td>
				<td>
					<?= (!empty($values[3]['value'])) ? $values[3]['value_short'] : ""; //год производства   ?>
				</td>
				<td>
					<?= (!empty($values[7]['value'])) ? number_format($values[7]['value_short'] / 1000, 1) . '&nbsp;л.' : ''; //объем двигателя  ?>
				</td>
				<td>
					<?php
					if (!empty($values[4]['value']))
						echo $values[4]['value'] . '<br>';
					if (!empty($values[5]['value']))
						echo $values[5]['value'] . '<br>';
					if (!empty($values[6]['value']))
						echo $values[6]['value'] . '<br>';
					?>
				</td>
				<td><?php
			if (!isset($values[8]['value']))
				echo '';
			elseif ($values[8]['value_short'] == 0)
				echo 'без пробега';
			else
				echo number_format($values[8]['value_short'], 0, '', '&nbsp;');
					?></td>
				<td><?php
			if ($advert['price'] && $advert['price'] != '-1')
				echo number_format($advert['price'], 0, '', '&nbsp;') . 'р.';
			else
				echo '&lt;не указано&gt;';
					?></td>
			</tr>
			<?php
		}
		$oddMeter = !$oddMeter;
	}
	?>
</table>



<div id="catPagination">
	<?php
	$this->widget('CLinkPager', array(
		'pages' => $page,
		'prevPageLabel' => '←',
		'nextPageLabel' => '→',
		'cssFile' => FALSE,
		'header' => '',
		'footer' => '',
		'htmlOptions' => array('class' => '')
	));
	?>
</div>
<div id="catPageSize">
	<?php $this->widget('application.widget.UPageSize', array('default' => Yii::app()->user->getState('catPageSize', 10))); ?>
</div>