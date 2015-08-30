<?php
namespace Ali\Controller;

use Ali\Base\ControllerAbstract;

class Error extends ControllerAbstract
{
	public function action404() {
		?><h2>404</h2><?php
	}
	public function actionPermission() {
		?><h2>Permissions</h2><?php
	}
}