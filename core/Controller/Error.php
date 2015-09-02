<?php
namespace Ali\Controller;

use Ali\Base\ControllerAbstract;

class Error extends ControllerAbstract
{
	public function action404() {
		$this->_view('404');
	}
	public function actionPermission() {
		$this->_view('permission');
	}
}