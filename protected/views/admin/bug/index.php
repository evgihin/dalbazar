<?php
/* @var $this BugController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Bugs',
);

$this->menu=array(
	array('label'=>'Create Bug', 'url'=>array('create')),
	array('label'=>'Manage Bug', 'url'=>array('admin')),
);
?>

<h1>Bugs</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
