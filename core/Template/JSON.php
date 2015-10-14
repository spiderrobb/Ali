<?php
namespace Ali\Template;

use Ali\Base\TemplateAbstract;

class JSON extends TemplateAbstract {
	public function build($args) {
		header('Content-Type: application/json');
		$args['content'] = $this->_view('Template', $args, true);
		parent::build($args);
	}
}
?>