<?php
namespace Ali\Controller;

use Ali\Base\ControllerAbstract;

class Index extends ControllerAbstract {
	public function actionIndex() {
		$this->_view('HelloWorld');
	}
}