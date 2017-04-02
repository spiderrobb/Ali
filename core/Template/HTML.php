<?php
namespace Ali\Template;

use Ali\Base\TemplateAbstract;
use Ali\Package;
class HTML extends TemplateAbstract {
	public function build($args) {
		ini_set('default_charset', 'UTF-8');
		header('Content-Type:text/html; charset=UTF-8');
		Package::getInstance()->addMeta(array(
			'http-equiv' => 'Content-Type',
			'content'    => 'tsext/html; charset=UTF-8'
		));
		$args['content'] = $this->_view('Template', $args, true);
		parent::build($args);
	}
}
