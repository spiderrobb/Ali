<?php
namespace Ali\Template;

use Ali\Base\TemplateAbstract;

class HTML extends TemplateAbstract {
	public function build($args) {
		$args['content'] = $this->_view('Template', $args, true);
		parent::build($args);
	}
}
?>