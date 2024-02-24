<?php /* @var $this AdminController */ ?>
<?php 
Yii::app()->clientScript->registerScriptFile("js/jquery-ui-1.9.1.custom.min.js"); 
Yii::app()->clientScript->registerCssFile("css/smoothness/jquery-ui-1.9.1.custom.min.css"); 
        ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="language" content="ru" />
		<base href="<?= Yii::app()->request->getBaseUrl(true) ?>">
		<link rel="stylesheet" type="text/css" href="css/main.css" />
		<link rel="stylesheet" type="text/css" href="css/admin.css" />
		<script src="js/jquery-1.8.2.min.js"></script>
		<title><?php echo CHtml::encode($this->pageTitle); ?></title>
	</head>

	<body>
		<div id="main">
			<div id="header">
				<div id="logo"><a href="/admin"><img src="images/theme/logo_admin.png" width="219" height="69"></a></div>
				<div id="mainmenu">
					<?php $this->widget('application.widget.UDbMenu'); ?>
				</div>
				<div id="title">
					Панель управления сайтом
				</div>
				<div id="loginblock">Блок логина/личной панели</div>
			</div><!-- header -->

			<?php if (isset($this->left)): ?>
				<div id="sidebar">
					<?php echo $this->left; ?>
				</div>
			<?php endif; ?>

			<div id="article" <?php if (isset($this->left)) echo 'class="withLeft"'; ?> >
				<?php if (isset($this->breadcrumbs) && count($this->breadcrumbs) > 0): ?>
					<div id="breadcrumbs">
						<?php
						$this->widget('zii.widgets.CBreadcrumbs', array(
							'links' => $this->breadcrumbs,
						));
						?>
					</div><!-- breadcrumbs -->
				<?php endif ?>
				<?php if (!$this->act->isEmpty()): ?>
					<div id="aActions">
						<?php
						$this->widget('application.controllers.admin.widget.actions', array(
							'actions' => $this->act,
						));
						?>
					</div>
				<?php endif ?>
			</div>
			<div id="section" <?php if (isset($this->left)) echo 'class="withLeft"'; ?> >

				<?php echo $content; ?>

			</div>
			<?php if (isset($this->left)): ?>
				<div class="clear"></div>
			<?php endif; ?>


			<div id="footer">
				&copy; <?php echo date('Y'); ?> Дальбазар.<br/>
				Все права защищены.<br/>
			</div><!-- footer -->
		</div><!-- page -->

	</body>
	<script>
		$('.list-view tr').click(function(){
			$(this).toggleClass('marked');
			$(this).find('input[type=checkbox]').each(function(){this.checked = !this.checked;});
		});
		$('.list-view input[type=checkbox]').change(function(event){
			$(this).parents('tr').eq(0).toggleClass('marked');
		});
		$('.list-view a,.list-view input[type=checkbox]').click(function(event){
			event.stopPropagation()
		});
		$('select.listFilter').change(function(){
			document.location = $(this).val();
		});
		$('table.list-sortable').sortable({
			items:'tr:has(td)',
			distance: 20,
			update:function(){
				var i=1;
				$(this).find('input.list-pos').each(function(){
					$(this).val(i);
					i++;
				})
			}
		});
		$('table.list-sortable td:not(:has(input))').disableSelection();
	</script>
</html>

