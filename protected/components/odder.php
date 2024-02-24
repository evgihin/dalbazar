<?php

class odder {

	private $_odd = false;

	public function tick() {
		$this->_odd = !$this->_odd;
		if (!$this->_odd)
			return 'even';
		else
			return 'odd';
	}

}