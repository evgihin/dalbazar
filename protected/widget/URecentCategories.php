<?php

class URecentCategories extends CWidget {

	public function init() {
//получить последние категории и самые частоиспользуемые категории из USER. Если их нет - получить из базы самые популярные
		$recent = new Category();
		$count = Yii::app()->params['countMenuItems'];
		$topUsed = $recent->getTopUsed($count);
		$command = Yii::app()->db->createCommand()
				->select('*')
				->from('category')
				->limit($count);
		if ($topUsed)
			$command
					->where(array('in', 'category_id', $topUsed))
					->order('Field(`category_id`,' . implode(',', $topUsed) . ')');
		$data = $command->query();
		//echo CHtml::openTag('ul');
		foreach ($data as $d) {
			echo CHtml::openTag('span');
			echo CHtml::link(str_replace(" ", "&nbsp;", $d['name']), 'category/' . $d['category_id']);
			echo CHtml::closeTag('span') . ' ';
		}
		//echo CHtml::closeTag('ul');
	}

}

;
