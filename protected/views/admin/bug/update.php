<?php
/* @var $this BugController */
/* @var $model Bug */

$this->breadcrumbs=array(
	'Bugs'=>array('index'),
	$model->bug_id=>array('view','id'=>$model->bug_id),
	'Update',
);

$this->menu=array(
	array('label'=>'List Bug', 'url'=>array('index')),
	array('label'=>'Create Bug', 'url'=>array('create')),
	array('label'=>'View Bug', 'url'=>array('view', 'id'=>$model->bug_id)),
	array('label'=>'Manage Bug', 'url'=>array('admin')),
);
?>

<h1>Update Bug <?php echo $model->bug_id; ?></h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>