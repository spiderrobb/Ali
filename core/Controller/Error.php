<?php
namespace Ali\Controller;

use Ali\Base\ControllerAbstract;

class Error extends ControllerAbstract
{
	public function action404() {
		// page not found
		$this->_view('404');
	}
	public function action403() {
		// permissions denied
		$this->_view('403');
	}
	public function action500() {
		// internal server error
		$this->_view('500');
	}
	public function action503() {
		// service unavailable
		$this->_view('503');
	}
}