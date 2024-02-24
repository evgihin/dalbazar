<?php
/* @var $this Controller */
/* @var $filters array */
/* @var $params array */
Yii::app()->clientScript->registerScriptFile('js/jquery-ui-1.9.1.custom.min.js');
Yii::app()->clientScript->registerCssFile('css/smoothness/jquery-ui-1.9.1.custom.min.css');
Yii::app()->clientScript->registerScriptFile('js/jquery.form.js');
Yii::app()->clientScript->registerScriptFile('js/uCategory.js')
?>
<?php if (count($filters)) { ?>
	<form id="filters" <?php /* action="<?= $this->createUrl('category/filter', array('categoryId' => $id)) ?>" */ ?> method="post">
		<div id="filterPanel" class="shadowed radiused">
			<?php
			$i = 0;
			$sliderIds = array();
			foreach ($filters as $filter) {
				?>
				<div
					class="filter"
					id="filter<?= $filter['filter_id'] ?>"
					<?= ($filter['filter_id']) ? ' data-depend="' . $filter['depend'] . '"' : "" ?>
					data-id="<?= $filter['filter_id'] ?>"
					>
					<div class="filterName <?php if ($i == 0) echo "firstFilter"; ?>">
						<?= $filter['name'] ?>
						<div class="filterClear"><a href="#" title="Сбросить фильтр">X</a></div>
					</div>
					<div class="filterParams">
						<?php
						switch ($filter['type']) {
							case "s":
								$this->renderPartial('filter_s', array(
									'filter' => $filter,
									'params' => $params[$filter['filter_id']],
								));
								break;
							case "i":
								$sliderIds[] = '#slider' . $filter['filter_id'];
								$this->renderPartial('filter_i', array(
									'filter' => $filter,
								));
								break;
						}
						?>
					</div>
				</div>
				<?php
				$i++;
			}
			?>
		</div>
		<input type="hidden" name="pagesize" id="pagesize" value="<?= Yii::app()->user->getState('catPageSize', 10) ?>">
	</form>
	<div id="submitFilter">применить</div>

	<script>
		var updateDepAddr = '<?= $this->createUrl('category/updateDependParams') ?>'; //адрес, который возвращает зависимые фильтры
		//слайдеры
		$( "<?= implode(', ', $sliderIds) ?>" ).each(function(){
			$(this).slider({
				range: true,
				min: parseInt($(this).siblings('.intSliderFrom').val()),
				max: parseInt($(this).siblings('.intSliderTo').val()),
				step: parseInt($(this).siblings('.intSliderStep').val()),
				values: [parseInt($(this).siblings('.intSliderFrom').val()),parseInt($(this).siblings('.intSliderTo').val())],
				slide: function( event, ui ) {
					$(this).siblings('.intSliderFrom').val(ui.values[ 0 ]);
					$(this).siblings('.intSliderTo').val(ui.values[ 1 ]);
					//showUpdButton($(this).parents('div.filter'));
				},
				change: function(event, ui){
					$(this).siblings('.intSliderFrom').val(ui.values[ 0 ]);
					$(this).siblings('.intSliderTo').val(ui.values[ 1 ]);
					$('#filters').submit();
				}
			});
		});
		$(".intSliderFrom").change( function(){$(this).siblings('.intSlider').slider( "values", 0 ,$(this).val());} );
		$(".intSliderTo").change( function(){$(this).siblings('.intSlider').slider( "values", 1 ,$(this).val());} );

	</script>
<?php } //endif   ?>

<?php // реклама $this->widget('application.widget.UBill');                 ?>
<div class="reklama1">
	<img src="images/reklama/preview.png">
</div>

<div id="lastadvert">
	<h3>Последние объявления (потоком)</h3>
	<?php $this->widget('application.widget.ULastAdvert'); ?>
</div>