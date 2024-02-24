<?php

class UPageSize extends CWidget {

	public $values;
	public $default;

	public function init() {
		if (empty($this->values))
			$this->values = Yii::app()->params['pageSizeValues'];
		echo CHtml::openTag('ul');
		foreach ($this->values as $value) {
			?>
			<li <?= (!empty($this->default) && $value == $this->default) ? 'class="selected"' : "" ?>><a href="#" data-size="<?= $value ?>"><?= $value ?></a></li>
			<?php
		}
		echo CHtml::closeTag('ul');
	}

}

;
