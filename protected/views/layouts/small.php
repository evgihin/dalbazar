<?php
/* @var $this Controller */
//Yii::app()->clientScript->registerScriptFile('js/jquery-1.8.0.min.js');
Yii::app()->clientScript->registerCssFile('css/main.css');
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="language" content="ru" />
		<base href="<?= Yii::app()->request->getBaseUrl(true) ?>">
		<script src="js/jquery-1.8.2.min.js"></script>
		<title><?php echo CHtml::encode($this->pageTitle); ?></title>
	</head>
	<body>

		<?php echo $content; ?>

		<div id="footer">
			&copy; <?php echo date('Y'); ?> Дальбазар.<br/>
			Все права защищены.<br/>
		</div><!-- footer -->
		<div id="loader"><div><img src="images/theme/loader.gif" width="64px" height="64px"></div></div>
		<script>
			$('#loader').ajaxSend(function(){
				$(this).show();
			}).ajaxStop(function(){
				$(this).hide();
			});
		</script>
	</body>
</html>