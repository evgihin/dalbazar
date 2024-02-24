<?php /* @var $this SiteController */ ?>
<?php

//@todo добавить отображение количества объявлений в скобках
function getAlias($cat) {
	if ($cat['alias'])
		return $cat['alias']; else
		return $cat['category_id'];
}
?>

<div id="categorylist">
	<ul id="maincatlist">
		<?php foreach ($categories as $cat) {
			?>
			<li><a href="<?= $this->createUrl('category/show', array('alias1' => getAlias($cat))) ?>"><img src="images/theme/category_icon/<?= ($cat['img']) ? $cat['img'] : 'empty.png' ?>" class="itemIcon"><?= $cat['name'] ?></a>


				<?php if (isset($subCategories[$cat['category_id']]) && count($subCategories[$cat['category_id']])) { ?>

					<div class="mainsubcatlist shadowed">
						<div class="bridger"></div>
						<?php if (isset($top) && isset($top[$cat['category_id']])): ?>
							<div class="columnBest">
								<ul>
									<?php
									foreach ($top[$cat['category_id']] as $t) {
										?>
										<li>
											<a href="<?= $this->createUrl('category/show', array('alias1' => getAlias($cat), 'alias2' => getAlias($t))) ?>">
												<img src="images/theme/category_icon/<?= ($t['img']) ? $t['img'] : 'empty.png' ?>" class="itemIcon"><?= $t['name'] ?>
											</a>
										</li>
									<?php } ?>
								</ul>
							</div>
							<?php
						endif;


						$i = 0;
						foreach ($subCategories[$cat['category_id']] as $subcat) {
							if ($i == 0) {
								?>
								<div class="column1">
									<ul>
									<?php }
									?>
									<a href="<?= $this->createUrl('category/show', array('alias1' => getAlias($cat), 'alias2' => getAlias($subcat))) ?>"><li><?= $subcat['name'] ?></li></a>
									<?php
									$i++;
									if ($i % Yii::app()->params['countSubPerBlock'] == 0) {
										?>
									</ul>
								</div>
								<div class="column<?= floor($i / Yii::app()->params['countSubPerBlock'] + 1) ?>">
									<ul>
										<?php
									}
								}
								?>
							</ul>
						</div>

						<?php if (isset($top) && isset($top[$cat['category_id']]) && count($top[$cat['category_id']]) < 7 && count($subCategories[$cat['category_id']]) >= Yii::app()->params['countSubPerBlock']): ?>
							<div class="reklama2"><img src="images/reklama/preview2.png"></div>
						<?php endif; ?>

					<?php } ?>
			</li>
			<?php
		}
		?>
	</ul>
</div>
<script>
	var timeout    = 500;
	var closetimer = 0;
	var ddmenuitem = 0;
	var ddelem = null;
	var debug=0;

	function jsddm_open() {
		if (!debug) jsddm_canceltimer();
		jsddm_close();
		ddmenuitem = $(this).find('div').eq(0).css({
			left:$(this).position().left+240,
			top:$(this).position().top
		}).show();
		ddelem=$(this).addClass('shadowed').addClass('hovered');
	}

	function jsddm_close() {
		if (ddmenuitem) ddmenuitem.hide();
		if (ddelem) ddelem.removeClass('shadowed').removeClass('hovered');
	}

	function jsddm_timer() {
		closetimer = window.setTimeout(jsddm_close, timeout);
	}

	function jsddm_canceltimer() {
		if (closetimer) {
			window.clearTimeout(closetimer);
			closetimer = null;
		}
	}

	$(document).ready(function() {
		$('#maincatlist > li').bind('mouseover', jsddm_open);
		if (!debug)   $('#maincatlist > li').bind('mouseout',  jsddm_timer);
	});
	if (!debug) document.onclick = jsddm_close;
</script>

<?php // реклама $this->widget('application.widget.UBill');       ?>
<div class="reklama1">
	<img src="images/reklama/preview.png">
</div>

<div id="lastadvert">
	<h3>Последние объявления (потоком)</h3>
	<?php $this->widget('application.widget.ULastAdvert'); ?>
</div>