<?php
/* @var $this SiteController */
$this->pageTitle='Дальбазар.ru - Доска объявлений Приморского края';
?>
<h1 id="titlebread">Категории:</h1>
<div id="categories">
  <?php
  $i=0;
  foreach ($categories as $category) { ?>
  <div class="mp_category">
    <?= CHtml::link('<img src="images/theme/category_icon/'.$category['img'].'">'.$category['name'], 'category/'.$category['category_id'])?>
  </div>

   <?php $i++;
    if ($i==2){
      $i=0;
      echo '<div class="clear"></div>';
    }
  }
  if ($i==1) echo '<div class="clear"></div>';
  ?>
</div>