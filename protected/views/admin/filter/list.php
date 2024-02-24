<?php 
/* @var $this AdminController */ 
/* @var $dependings array */ 

?>
<h1 class="title">Список доступных фильтров</h1>

категория: <select class="listFilter">
  <option value="<?= $this->createUrl('admin/filter/list', array('categoryId' => 'all')) ?>" <?= ($categoryId == 'all') ? 'selected=""' : '' ?> >(все категории)</option>
  <?php foreach ($categories[0] as $category) { ?>
    <option value="<?= $this->createUrl('admin/filter/list', array('categoryId' => $category['category_id'])) ?>" <?= ($categoryId == $category['category_id']) ? 'selected=""' : '' ?>>
      <?= $category['name'] ?>
    </option>
    <?php
    if (isset($categories[$category['category_id']]))
      foreach ($categories[$category['category_id']] as $subCategory) {
        ?>
        <option value="<?= $this->createUrl('admin/filter/list', array('categoryId' => $subCategory['category_id'])) ?>" <?= ($categoryId == $subCategory['category_id']) ? 'selected=""' : '' ?>>
          ---<?= $subCategory['name'] ?>
        </option>
        <?php
      }
  }
  ?>
  <option value="<?= $this->createUrl('admin/filter/list', array('categoryId' => 'removed')) ?>" <?= ($categoryId == 'removed') ? 'selected=""' : '' ?> >(корзина)</option>
</select><br>

<form  id="filterList" method="post" action="<?= $this->createUrl('admin/filter/do') ?>">
  <table style="width: 100%" class="list-view<?=(is_int($categoryId))?' list-sortable':''?>">
    <tr>
      <th width="1px"></th>
      <th>ИД</th>
      <th>Имя фильтра</th>
      <th>Тип</th>
      <?php if (is_int($categoryId)): ?>
        <th>Порядок</th>
      <?php endif; ?>
      <th>Зависит от</th>
      <th>В категориях</th>
    </tr>
    <?php
    $i = 0;
    foreach ($filters as $filter) {
      $i++;
      ?>
      <tr class="<?= ($i % 2) ? 'odd' : 'even' ?>">
        <td><input type="checkbox" name="select[<?= $filter['filter_id'] ?>]"></td>
        <td><?= $filter['filter_id'] ?></td>
        <td>
          <a href="<?= $this->createUrl('admin/filter/edit', array('filterId' => $filter['filter_id'])) ?>">
            <?= $filter['name'] ?> (<?= (int) $filter['params'] ?>)
          </a>
        </td>
        <td><?= ($filter['type'] == 's') ? 'выбор' : 'число'; ?></td>
        <?php if (is_int($categoryId)): ?>
          <td>
            <input
              type="text"
              size="2"
              name="pos[<?= $filter['filter_id'] ?>]"
              value="<?= $filter['pos'] ?>"
              class="list-pos"
              autocomplete="off">
          </td>
        <?php endif; ?>
          <td><a href="<?=$this->createUrl('admin/filter/depend', array('filterId'=>$filter['filter_id']))?>"><?= $filter['depend_name'] ?></a></td>
          <td class="very-small"><?php
          if (isset($dependings[$filter['filter_id']]))
              echo implode("<br>",$dependings[$filter['filter_id']]);
          ?></td>
      </tr>
    <?php } ?>
  </table>
</form>