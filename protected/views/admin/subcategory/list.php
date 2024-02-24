<?php
/**
 * @var $this CategoryController
 * @var $categories array
 */
$this->act
        ->delete('categories')
        ->add('admin/subcategory/add');

if ($parent_id !== NULL)
	$this->act->save('categories');
$odder = new odder();
?>
<h1>Редактирование подкатегорий</h1>
Родительская категория: <select class="listFilter">
	<option value="<?= $this->createUrl('admin/subcategory/list') ?>" <?= ($parent_id === NULL) ? 'selected=""' : '' ?> >(все категории)</option>
		<?php foreach ($parents as $parent) { ?>
		<option value="<?= $this->createUrl('admin/subcategory/list', array('parent_id' => $parent['category_id'])) ?>" <?= ($parent_id == $parent['category_id']) ? 'selected=""' : '' ?>>
		<?= $parent['name'] ?>
		</option>
		<?php
	}
	?>
</select><br>
<form id="categories" action="<?= $this->createUrl('admin/subcategory/do') ?>" method="post">
	<table style="width: 100%;" <?php if ($parent_id !== NULL) echo 'class="list-sortable"' ?>>
		<tr>
			<th class="minCol"></th>
			<th class="minCol">#</th>
			<th class="minCol"></th>
			<th>Алиас</th>
			<th>Имя</th>
			<th>Родитель</th>
		<?php if ($parent_id !== NULL) echo '<th class="minCol">Позиция</th>'; ?>
		</tr>
		<?php
		foreach ($categories as $category) {
			$editUrl = $this->createUrl('admin/subcategory/edit', array('category_id' => $category['category_id']));
			$editParentUrl = $this->createUrl('admin/category/edit', array('category_id' => $parents[$category['category_parent_id']]['category_id']));
			?>
			<tr class="<?= $odder->tick() ?>">
				<td><input type="checkbox" name="select[<?= $category['category_id'] ?>]"></td>
				<td><?= $category['category_id'] ?></td>
				<td><?= CHtml::image('images/theme/category_icon/' . $category['img']) ?></td>
				<td><?= $category['alias'] ?></td>
				<td><a href="<?= $editUrl ?>"><?= $category['name'] ?></a></td>
				<td><a href="<?= $editParentUrl ?>"><?= $parents[$category['category_parent_id']]['name'] ?></a></td>
	<?php if ($parent_id !== NULL) { ?>
					<td>
						<input type="text" class="list-pos" size="3" name="pos[<?= $category['category_id'] ?>]" value="<?= $category['pos'] ?>">
					</td>
			<?php } ?>
			</tr>
<?php } ?>
	</table>
</form>