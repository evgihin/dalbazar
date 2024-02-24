<?php
/* @var $this SiteController */
/* @var $lastest array - массив последних добавленных объявлений */
$this->pageTitle = Yii::app()->name.' - Доска объявлений Приморского края';
?>

<h1 id="titlebread">Недавно добавленные объявления</h1>
<div id="categories"><?php 
  $this->widget("application.widget.UAdvertBegun",array("items"=>$lastest,"count"=>5,"class"=>'my'));
  ?>
</div>
<h1 id="titlebread">Часто посещаемые объявления</h1>
<div id="categories">
<?php 
  $this->widget("application.widget.UAdvertBegun",array("items"=>$topViews,"count"=>5,"class"=>'my'));

  ?>
</div><!--
<h1 id="titlebread">Возможно, Вас заинтересует</h1>
<div id="categories">

<?php 
  $this->widget("application.widget.UAdvertBegun",array("items"=>array(
      array(
          "img"=>"http://dalbazar.ru/images/advert/resize/120_120_08ba4273d09afb4640f86164310e9a5e.jpg",
          "sdescription"=>"500 005р.", 
          "description"=>"Продам кожанный ауди1 бла бла бла бла бла бла бла", 
          "href" => "http://dalbazar.ru/advert/show/advert_id/51.aspx"),
      array(
          "img"=>"http://dalbazar.ru/images/advert/resize/120_120_08ba4273d09afb4640f86164310e9a5e.jpg",
          "sdescription"=>"500 004р.", "description"=>"Продам кожанный ауди2", "href" => "http://dalbazar.ru/advert/show/advert_id/52.aspx"),
      array(
          "img"=>"http://dalbazar.ru/images/advert/resize/120_120_08ba4273d09afb4640f86164310e9a5e.jpg",
          "sdescription"=>"500 003р.", 
          "description"=>"супер пупер длинная проверка длины обрезаемого текста для подписи объектов в бегуне. Я думаю, что тут наберется больше ста символов и нам придется обрезать их дополнительными средствами.", 
          "href" => "http://dalbazar.ru/advert/show/advert_id/53.aspx"),
      array(
          "img"=>"http://dalbazar.ru/images/advert/resize/120_120_08ba4273d09afb4640f86164310e9a5e.jpg",
          "sdescription"=>"500 002р.", 
          "description"=>"Продам кожанный ауди4", 
          "href" => "http://dalbazar.ru/advert/show/advert_id/54.aspx"
          ),
      array("img"=>"http://dalbazar.ru/images/advert/resize/120_120_08ba4273d09afb4640f86164310e9a5e.jpg","sdescription"=>"500 001р.", "description"=>"Продам кожанный ауди5", "href" => "http://dalbazar.ru/advert/show/advert_id/55.aspx"),
      array("img"=>"http://dalbazar.ru/images/advert/resize/120_120_08ba4273d09afb4640f86164310e9a5e.jpg","sdescription"=>"500 001р.", "description"=>"Продам кожанный ауди5", "href" => "http://dalbazar.ru/advert/show/advert_id/55.aspx"),
      array("img"=>"http://dalbazar.ru/images/advert/resize/120_120_08ba4273d09afb4640f86164310e9a5e.jpg","sdescription"=>"500 001р.", "description"=>"Продам кожанный ауди5", "href" => "http://dalbazar.ru/advert/show/advert_id/55.aspx"),
      array("img"=>"http://dalbazar.ru/images/advert/resize/120_120_08ba4273d09afb4640f86164310e9a5e.jpg","sdescription"=>"500 001р.", "description"=>"Продам кожанный ауди5", "href" => "http://dalbazar.ru/advert/show/advert_id/55.aspx"),
      array("img"=>"http://dalbazar.ru/images/advert/resize/120_120_08ba4273d09afb4640f86164310e9a5e.jpg","sdescription"=>"500 001р.", "description"=>"Продам кожанный ауди5", "href" => "http://dalbazar.ru/advert/show/advert_id/55.aspx"),
      array("img"=>"http://dalbazar.ru/images/advert/resize/120_120_08ba4273d09afb4640f86164310e9a5e.jpg","sdescription"=>"500 001р.", "description"=>"Продам кожанный ауди5", "href" => "http://dalbazar.ru/advert/show/advert_id/55.aspx"),
      array("img"=>"http://dalbazar.ru/images/advert/resize/120_120_08ba4273d09afb4640f86164310e9a5e.jpg","sdescription"=>"500 001р.", "description"=>"Продам кожанный ауди5", "href" => "http://dalbazar.ru/advert/show/advert_id/55.aspx"),
  ),"count"=>5,"class"=>'my'));

  ?>
</div>
-->