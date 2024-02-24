<?php
/**
 * @var $this CategoryController
 * @var $categories array
 */
$this->act
        ->delete('categories')
        ->add('admin/category/add')
        ->save('categories');

$odder = new odder();
?>
<h1>Редактирование основных категорий</h1>
<form id="categories" action="<?= $this->createUrl('admin/category/do') ?>" method="post">
	<table style="width: 100%;" class="list-sortable">
		<tr>
			<th class="minCol"></th>
			<th class="minCol">#</th>
			<th class="minCol"></th>
			<th>Алиас</th>
			<th>Имя</th>
			<th class="minCol">Позиция</th>
		</tr>
		<?php
		foreach ($categories as $category) {
			$editUrl = $this->createUrl('admin/category/edit', array('category_id' => $category['category_id']));
			?>
			<tr class="<?= $odder->tick() ?>">
				<td><input type="checkbox" name="select[<?= $category['category_id'] ?>]"></td>
				<td><?= $category['category_id'] ?></td>
				<td><?= CHtml::image('images/theme/category_icon/' . $category['img']) ?></td>
				<td><?= $category['alias'] ?></td>
				<td><a href="<?= $editUrl ?>"><?= $category['name'] ?></a></td>
				<td><input type="text" class="list-pos" size="3" name="pos[<?= $category['category_id'] ?>]" value="<?= $category['pos'] ?>"></td>
			</tr>
		<?php } ?>
	</table>
</form>