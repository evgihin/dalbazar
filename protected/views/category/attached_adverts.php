<?php
/* @var $this CategoryController */
/* @var $adverts array */
/* @var $values array */
foreach ($adverts as $advert) {
	$advertId = $advert['advert_id'];
	?><a href="<?= $this->createUrl('advert/show', array('advert_id' => $advertId)) ?>"><div class="atBlock">
			<div class="atImage radiused"><img src="<?= Helpers::getImageUrl($advert['image'], 120, 120) ?>"></div>
			<div class="atText"><?php
	if (!empty($values[$advertId][1]['value'])) {
		echo $values[$advertId][1]['value'];
		if (!empty($values[$advertId][2]['value']))
			echo " " . $values[$advertId][2]['value'];
	}
	if (!empty($values[$advertId][3]['value']))
		echo ' ' . $values[$advertId][3]['value'];
	?></div>
			<?php if ($advert['price'] && $advert['price'] != '-1') { ?>
				<div class="atPrice radiused"><?= number_format($advert['price'], 0, '', '&nbsp;') . 'р.'; ?></div>
			<?php } ?>
		</div></a><?php
	}
	if (count($adverts) < 5) {
		for ($i = 0; $i < 5 - count($adverts); $i++) {
				?><a href="<?= $this->createUrl('advert/sms') ?>"><div class="atBlock">
				<div class="atImage radiused"><img src="images/theme/advertAdd.png"></div>
				<div class="atText">разместить здесь своё объявление</div>
			</div></a>
		<?php
	}
	?>
<div class="atComment" style="display: none;">
	Для размещения объявления в спец. ленте отправьте СМС с номером своего объявления на короткий номер <b>8385</b>.<br>
	Внимание! Услуга платная! <a href="http://smsrent.ru/tariffs/RU/8385/" target="blank">ознакомиться с ценами</a>.<br><br>
	<div class="atHide">скрыть X</div>
</div>
	<script>
		$('div#advAttached>a').click(function(){
			$('.atComment').show(300);
			$('div#advAttached>a').hide(300);
			return false;
		});
		$('.atHide').click(function(){
			$('.atComment').hide(300);
			$('div#advAttached>a').show(300);
			return false;
		});
	</script><?php
}
?>