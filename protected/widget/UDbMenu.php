<?php

class UDbMenu extends CWidget {

	public function init() {
		$data = Yii::app()->db->cache(1440)->createCommand()->select('*')->from('mainmenu')->order("pos ASC")->query();
		echo CHtml::openTag('ul');
		foreach ($data as $d) {
			echo CHtml::openTag('li');
			if ($d['uri'] == 'bug')
				echo CHtml::link($d['text'], $d['uri'], array('class' => 'red'));
			else
				echo CHtml::link($d['text'], $d['uri']);
			echo CHtml::closeTag('li');
		}
		echo CHtml::closeTag('ul');
	}

}

;