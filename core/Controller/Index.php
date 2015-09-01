<?php
namespace Ali\Controller;

use Ali\Base\ControllerAbstract;

class Index extends ControllerAbstract {
	public function actionIndex() {
		?><pre><?php
		var_dump($this->_input);
		?></pre><?php
	}
}