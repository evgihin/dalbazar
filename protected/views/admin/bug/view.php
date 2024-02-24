<?php
/* @var $this BugController */
/* @var $model Bug */

$this->breadcrumbs=array(
	'Bugs'=>array('index'),
	$model->bug_id,
);

$this->menu=array(
	array('label'=>'List Bug', 'url'=>array('index')),
	array('label'=>'Create Bug', 'url'=>array('create')),
	array('label'=>'Update Bug', 'url'=>array('update', 'id'=>$model->bug_id)),
	array('label'=>'Delete Bug', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->bug_id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Bug', 'url'=>array('admin')),
);
?>

<h1>View Bug #<?php echo $model->bug_id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'bug_id',
		'link',
		'text',
		'creation',
		'user_agent',
		'ip',
		'solved',
		'note',
	),
)); ?>
