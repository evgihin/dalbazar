<?php /* @var $this AdminController */ ?>
<?php
$this->act
        ->close('admin/filter/list')
        ->apply('dependList')
        ->save('dependList')
        ->extendLink('addParams', array(
			'admin/param/addDepend',
			'filter_id' => $id), 'добавить<br>зависимости', 'images/theme/icon-32-new.png');
 
?>
<h1 class="title">Редактирование зависимости фильтра</h1>
<form  id="dependList" method="post" action="<?= $this->createUrl('admin/filter/saveDepend', array('filterId' => $id)) ?>">
	<?php
	foreach ($params as $param) {
		?>
	    <label for="depend<?= $param['filter_param_id'] ?>"><?= $param['name'] ?>:</label>
	    <select name="depend[<?= $param['filter_param_id'] ?>]">
			<?php foreach ($dParams as $dParam) { ?>
		        <option
					value="<?= $dParam['filter_param_id'] ?>"
					<?php
					if (
							isset($depends[$param['filter_param_id']]['filter_depending_param_id']) &&
							$dParam['filter_param_id'] == $depends[$param['filter_param_id']]['filter_depending_param_id']
					)
						echo 'selected';
					?>><?= $dParam['name'] ?></option>
		<?php } ?>
	    </select><br>
<?php }
?>
</form>